<?php
/**
 *
 * This file is part of the "FS19 Web Stats" package.
 * Copyright (C) 2017-2019 John Hawk <john.hawk@gmx.net>
 *
 * "FS19 Web Stats" is free software: you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * "FS19 Web Stats" is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
if (! defined ( 'IN_FS19WS' )) {
	exit ();
}
class Animals {
	private static $farmId;
	private static $xml;
	private static $stables = array ();
	public static function loadStables($xml) {
		self::$farmId = $_SESSION ['farmId'];
		self::$xml = $xml;
		self::analyzeItems ();
	}
	private static function analyzeItems() {
		foreach ( self::$xml ['items'] as $item ) {
			$id = strval ( $item ['id'] );
			$stable = cleanFileName ( $item ['filename'] );
			$l_stable = translate ( $stable );
			
			if ($item ['className'] == 'AnimalHusbandry' && $item ['farmId'] == self::$farmId) {
				$productivity = floatval ( $item ['globalProductionFactor'] ) * 100;
				self::$stables [$id] = array (
						'forHorses' => false,
						'name' => $l_stable,
						'productivity' => floor ( $productivity ),
						'numberOfAnimals' => 0,
						'animals' => array (),
						'state' => array () 
				
				);
				
				foreach ( $item->module as $module ) {
					switch ($module ['name']) {
						case 'animals' :
							foreach ( $module->animal as $animal ) {
								$animalType = strval ( $animal ['fillType'] );
								self::$stables [$id] ['numberOfAnimals'] ++;
								if (stristr ( $animalType, 'HORSE' ) !== false) {
									// Animal is a horse
									$horseName = strval ( $animal ['name'] );
									$horseNameURL = urlencode ( $horseName );
									$fitnessScale = floatval ( $animal ['fitnessScale'] );
									$healthScale = floatval ( $animal ['healthScale'] );
									$dirtScale = floatval ( $animal ['dirtScale'] );
									self::$stables [$id] ['animals'] [$horseNameURL] = array (
											'name' => $horseName,
											'fitnessScale' => floor ( $fitnessScale * 100 ),
											'healthScale' => floor ( $healthScale * 100 ),
											'dirtScale' => floor ( (1 - $dirtScale) * 100 ),
											'ridingTimer' => self::getRideTime(floatval ( $animal ['ridingTimer'] )),
											'value' => 49500 * $fitnessScale * $healthScale + 500 * (1 - $dirtScale),
											'isHorse' => true,
											'image' => $animalType 
									);
								} else {
									// Animal is not a Horse
									$l_animal = translate ( $animalType );
									if (isset ( self::$stables [$id] ['animals'] [$animalType] )) {
										self::$stables [$id] ['animals'] [$animalType] ['count'] ++;
									} else {
										self::$stables [$id] ['animals'] [$animalType] = array (
												'name' => $l_animal,
												'count' => 1,
												'breeding' => 0,
												'reproRate' => '--:--',
												'nextAnimal' => '--:--',
												'isHorse' => false,
												'image' => $animalType 
										);
									}
								}
							}
							foreach ( $module->breeding as $breeding ) {
								$animal = strval ( $breeding ['fillType'] );
								$count = self::$stables [$id] ['animals'] [$animal] ['count'];
								if ($count > 1 && $productivity != 0) {
									$l_animal = translate ( $animal );
									$breeding = floatval ( $breeding ['percentage'] );
									$reproRate = ceil ( (self::getReproRate ( $animal ) / $count * 3600 * 100 / $productivity) / 900 ) * 900;
									$nextAnimal = ceil ( ($reproRate * (1 - $breeding)) / 900 ) * 900;
									self::$stables [$id] ['animals'] [$animal] ['reproRate'] = self::getTimeString ( $reproRate );
									self::$stables [$id] ['animals'] [$animal] ['nextAnimal'] = self::getTimeString ( $nextAnimal );
								}
							}
							break;
						case 'foodSpillage' :
							$cleanlinessFactor = floatval ( $module ['cleanlinessFactor'] );
							self::$stables [$id] ['state'] ['foodSpillage'] = array (
									'name' => '##CLEANLINESS##',
									'value' => floor ( $cleanlinessFactor * 100 ),
									'unit' => '%',
									'factor' => floor ( $cleanlinessFactor * 100 ) 
							);
							break;
						case 'straw' :
							$fillType = strtoupper ( $module ['name'] );
							$fillCapacity = floatval ( $module ['fillCapacity'] );
							$fillLevel = floatval ( $module->fillLevel ['fillLevel'] );
							
							$factor = self::calculateFactor ( $fillLevel, $fillCapacity );
							
							self::$stables [$id] ['state'] [strval ( $module ['name'] )] = array (
									'name' => translate ( 'STRAW' ),
									'value' => floor ( $fillLevel ),
									'unit' => 'l',
									'factor' => $factor 
							);
							break;
						case 'water' :
							$fillCapacity = floatval ( $module ['fillCapacity'] );
							$fillLevel = floatval ( $module->fillLevel ['fillLevel'] );
							
							$factor = self::calculateFactor ( $fillLevel, $fillCapacity );
							
							self::$stables [$id] ['state'] ['water'] = array (
									'name' => translate ( 'WATER' ),
									'value' => floor ( $fillLevel ),
									'unit' => 'l',
									'factor' => $factor 
							);
							break;
						case 'manure' :
							$fillCapacity = floatval ( $module ['fillCapacity'] );
							$fillLevel = floatval ( $module ['manureToDrop'] );
							
							$factor = self::calculateFactor ( $fillLevel, $fillCapacity );
							
							self::$stables [$id] ['product'] ['manure'] = array (
									'name' => translate ( 'MANURE' ),
									'value' => floor ( $fillLevel ),
									'unit' => 'l',
									'factor' => $factor 
							);
							break;
						case 'liquidManure' :
							$fillCapacity = floatval ( $module ['fillCapacity'] );
							$fillLevel = floatval ( $module->fillLevel ['fillLevel'] );
							
							$factor = self::calculateFactor ( $fillLevel, $fillCapacity );
							
							self::$stables [$id] ['product'] ['liquidManure'] = array (
									'name' => translate ( 'LIQUIDMANURE' ),
									'value' => floor ( $fillLevel ),
									'unit' => 'l',
									'factor' => $factor 
							);
							break;
						case 'milk' :
							$fillCapacity = floatval ( $module ['fillCapacity'] );
							$fillLevel = floatval ( $module->fillLevel ['fillLevel'] );
							
							$factor = self::calculateFactor ( $fillLevel, $fillCapacity );
							
							self::$stables [$id] ['product'] ['milk'] = array (
									'name' => translate ( 'MILK' ),
									'value' => floor ( $fillLevel ),
									'unit' => 'l',
									'factor' => $factor 
							);
							break;
						case 'pallets' :
							$fillLevel = floatval ( $module ['palletFillDelta'] );
							self::$stables [$id] ['product'] ['wool'] = array (
									'name' => translate ( 'WOOL' ), // If they're eggs, it'll be changed below
									'value' => floor ( $fillLevel ),
									'unit' => 'l',
									'factor' => 100 
							);
							break;
						case 'food' :
							foreach ( $module->fillLevel as $trough ) {
								$fillType = strval ( $trough ['fillType'] );
								$fillLevel = $trough ['fillLevel'];
								self::$stables [$id] ['food'] [$fillType] = array (
										'name' => translate ( $fillType ),
										'value' => floor ( floatval($fillLevel) ),
										'unit' => 'l',
										'factor' => 100 
								);
							}
							break;
					}
				}
				$food = array_keys ( self::$stables [$id] ['food'] ); // troughs
				if (in_array ( 'MAIZE', $food )) {
					self::calculateTrougs ( $id, 'pig' );
				} elseif (in_array ( 'FORAGE', $food )) {
					self::calculateTrougs ( $id, 'cow' );
				} elseif (in_array ( 'OAT', $food )) {
					self::$stables [$id] ['forHorses'] = true;
					self::calculateTrougs ( $id, 'horse' );
				} elseif (in_array ( 'GRASS_WINDROW', $food )) {
					self::calculateTrougs ( $id, 'sheep' );
				} else {
					self::$stables [$id] ['product'] ['egg'] = self::$stables [$id] ['product'] ['wool'];
					self::$stables [$id] ['product'] ['egg'] ['name'] = translate ( 'EGG' );
					unset ( self::$stables [$id] ['product'] ['wool'] );
					self::calculateTrougs ( $id, 'chicken' );
				}
			}
		}
		
		// print_r(array_values(self::$stables));
	}
	public static function getHorseValues() {
		$ret = array (
				'summary' => 0,
				'animals' => array () 
		);
		foreach ( self::$stables as $stable ) {
			if ($stable ['forHorses']) {
				foreach ( $stable ['animals'] as $animal ) {
					$ret ['summary'] += $animal ['value'];
					$ret ['animals'] [$animal ['name']] = $animal ['value'];
				}
			}
		}
		return $ret;
	}
	public static function calculateFactor($fillLevel, $fillCapacity) {
		if ($fillCapacity > 0) {
			$factor = floor ( $fillLevel / $fillCapacity * 100 );
		} else {
			$factor = 0;
		}
		
		return $factor;
	}
	public static function getStables() {
		return self::$stables;
	}
	private static function calculateTrougs($stable, $animal) {
		$troughs = array (
				'pig' => array (
						'trough1' => array (
								'MAIZE' 
						),
						'trough2' => array (
								'WHEAT',
								'BARLEY' 
						),
						'trough3' => array (
								'SOYBEAN',
								'CANOLA',
								'SUNFLOWER' 
						),
						'trough4' => array (
								'POTATO',
								'SUGARBEET' 
						) 
				),
				'cow' => array (
						'trough1' => array (
								'FORAGE' 
						),
						'trough2' => array (
								'DRYGRASS_WINDROW',
								'SILAGE' 
						),
						'trough3' => array (
								'GRASS_WINDROW' 
						) 
				),
				'sheep' => array (
						'trough1' => array (
								'GRASS_WINDROW',
								'DRYGRASS_WINDROW' 
						) 
				),
				'chicken' => array (
						'trough1' => array (
								'WHEAT',
								'BARLEY' 
						) 
				),
				'horse' => array (
						'trough1' => array (
								'OAT' 
						),
						'trough2' => array (
								'DRYGRASS_WINDROW' 
						) 
				) 
		);
		foreach ( $troughs [$animal] as $trough => $troughFoods ) {
			foreach ( $troughFoods as $food ) {
				$consumptionPerWeek = self::getConsumptionPerWeekAndAnimal ( $animal ) * self::$stables [$stable] ['numberOfAnimals'];
				if (! isset ( self::$stables [$stable] ['trough'] [$trough] )) {
					$foodValue = self::$stables [$stable] ['food'] [$food] ['value'];
					$factor = self::calculateFactor ( $foodValue, $consumptionPerWeek );
					self::$stables [$stable] ['trough'] [$trough] = array (
							'name' => translate ( $food ),
							'value' => $foodValue,
							'unit' => 'l',
							'factor' => $factor 
					);
				} else {
					self::$stables [$stable] ['trough'] [$trough] ['name'] .= ' / ' . translate ( $food );
					$foodValue = self::$stables [$stable] ['trough'] [$trough] ['value'] + self::$stables [$stable] ['food'] [$food] ['value'];
					$factor = self::calculateFactor ( $foodValue, $consumptionPerWeek );
					self::$stables [$stable] ['trough'] [$trough] ['value'] = $foodValue;
					self::$stables [$stable] ['trough'] [$trough] ['factor'] = $factor;
				}
			}
		}
	}
	private static function getConsumptionPerWeekAndAnimal($animal) {
		$consumptionPerWeek = array (
				'chicken' => 50,
				'cow' => 3500,
				'horse' => 4000,
				'pig' => 900,
				'sheep' => 500 
		);
		if (isset ( $consumptionPerWeek [$animal] )) {
			return $consumptionPerWeek [$animal];
		}
		return 0;
	}
	private static function getReproRate($animalName) {
		if (stristr ( $animalName, 'COW' ) !== false) {
			return 1200;
		}
		if (stristr ( $animalName, 'PIG' ) !== false) {
			return 144;
		}
		if (stristr ( $animalName, 'SHEEP' ) !== false) {
			return 960;
		}
		if (stristr ( $animalName, 'CHICKEN' ) !== false) {
			return 240;
		}
		return 999;
	}
	private static function getTimeString($time) {
		$hours = (gmdate ( "d", $time ) - 1) * 24 + gmdate ( "H", $time );
		return $hours . ':' . gmdate ( "i", $time );
	}
	private static function getRideTime($rideTime){
		
		if ($rideTime > 300000) {
			return 100;
		}
		else{
			return floor (( $rideTime / 300000 ) * 100 );
		}
	}
}