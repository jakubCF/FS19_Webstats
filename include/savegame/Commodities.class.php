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
class Commodity {
	const FILLTYPES_TO_IGNORE = array (
			'AIR',
			'DEF',
			'ROUNDBALE',
			'SQUAREBALE',
			'UNKNOWN'
	);
	private $overall;
	private $i3dName;
	private $isCombine;
	private $locations;
	private $outOfMap = false;
	private static $xml;
	private static $farmId;
	public static $commodities = array ();
	public static $commoditiesArray = array ();
	public static $outOfMapArray = array ();
	public static $positions = array ();
	public static function loadCommodities($xml) {
		self::$farmId = $_SESSION ['farmId'];
		self::$xml = $xml;
		self::loadVehicles ();
		self::analyzeItems ();
	}
	public static function getAllCommodities() {
		ksort ( self::$commoditiesArray );
		return self::$commoditiesArray;
	}
	public static function getAllOutOfMap() {
		return self::$outOfMapArray;
	}
	private static function analyzeItems() {
		global $gameData;
		foreach ( self::$xml ['items'] as $item ) {
		 	$location = cleanFileName ( $item ['filename'] );
			switch ($item ['className']) {
				case 'FS19_GlobalCompany.GC_ProductionFactoryPlaceable' :
					if ($item ['farmId'] == $_SESSION ['farmId']) {
						if (isset ( $item->productionFactory->outputProducts )) {
							foreach ( $item->productionFactory->outputProducts->outputProduct as $product ) {
								if (isset ($product->palletCreator)){
									//do not count quantity in production factory if it creates pallets
									continue;
								}
								$trigger = strval ( $product ['name'] );
								// if(isset($gameData['objects'][$location]))
								$fillType = strval ( $product ['name'] );
								$fillLevel = intval ( $product ['fillLevel'] );
								self::addCommodity ( $fillType, $fillLevel, $location );
							}
						}
					}
					break;
				case 'Bale' :
					if ($item ['farmId'] == $_SESSION ['farmId']) {
						$location = getLocation ( $item ['position'] );
						$fillType = cleanFileName ( $item ['filename'] );
						$fillLevel = intval ( $item ['fillLevel'] );
						self::addCommodity ( $fillType, $fillLevel, $location, strval ( $item ['className'] ) );
					}
					break;
				case 'AnimalHusbandry' :
					if ($item ['farmId'] == $_SESSION ['farmId']) {
						foreach ( $item->module as $module ) {
							switch ($module ['name']) {
								case 'animals' :
									foreach ( $module->animal as $animal ) {
										$fillType = strval ( $animal ['fillType'] );
										self::addCommodity ( $fillType, 1, $location, 'animal' );
									}
									break;
								case 'liquidManure' :
									$fillLevel = intval ( $module->fillLevel ['fillLevel'] );
									self::addCommodity ( 'LIQUIDMANURE', $fillLevel, $location );
									break;
								case 'milk' :
									$fillLevel = intval ( $module->fillLevel ['fillLevel'] );
									self::addCommodity ( 'MILK', $fillLevel, $location );
									break;
							}
						}
					}
					break;
				case 'BgaPlaceable' :
					foreach ( $item->bga->digestateSilo->storage as $storage ) {
						if ($item ['farmId'] == $_SESSION ['farmId']) {
							foreach ( $storage as $node ) {
								$fillType = strval ( $node ['fillType'] );
								$fillLevel = intval ( $node ['fillLevel'] );
								self::addCommodity ( $fillType, $fillLevel, $location );
							}
						}
					}
					/*
					 * Planed: Check if bunkersilo is on farmland the farm owned
					 */
					if ($item ['farmId'] == $_SESSION ['farmId']) {
						foreach ( $item->bunkerSilo as $bunkerSilo ) {
							$state = intval ( $bunkerSilo ['state'] );
							$fillLevel = intval ( $bunkerSilo ['fillLevel'] );
							$compactedFillLevel = intval ( $bunkerSilo ['compactedFillLevel'] );
							switch ($state) {
								case 0 :
									self::addCommodity ( 'CHAFF', $fillLevel, $location );
									break;
								case 1 :
									self::addCommodity ( 'CHAFF', $compactedFillLevel, $location );
									break;
								case 2 :
								case 3 :
									self::addCommodity ( 'SILAGE', $fillLevel, $location );
									break;
							}
						}
					}
					break;
				case 'BunkerSiloPlaceable' :
					if ($item ['farmId'] == $_SESSION ['farmId']) {
						foreach ( $item->bunkerSilo as $bunkerSilo ) {
							$state = intval ( $bunkerSilo ['state'] );
							$fillLevel = intval ( $bunkerSilo ['fillLevel'] );
							$compactedFillLevel = intval ( $bunkerSilo ['compactedFillLevel'] );
							switch ($state) {
								case 0 :
									self::addCommodity ( 'CHAFF', $fillLevel, $location );
									break;
								case 1 :
									self::addCommodity ( 'CHAFF', $compactedFillLevel, $location );
									break;
								case 2 :
								case 3 :
									self::addCommodity ( 'SILAGE', $fillLevel, $location );
									break;
							}
							/*
							self::addCommodity ( 'CHAFF', ($state < 2) ? $fillLevel : 0, $location );
							self::addCommodity ( 'SILAGE', ($state < 2) ? 0 : $fillLevel, $location );
							*/
						}
					}
					break;
				case 'SiloPlaceable' :
				case 'SiloExtensionPlaceable' :
					foreach ( $item as $storage ) {
						if ($storage ['farmId'] == $_SESSION ['farmId']) {
							foreach ( $storage as $node ) {
								$fillType = strval ( $node ['fillType'] );
								$fillLevel = intval ( $node ['fillLevel'] );
								self::addCommodity ( $fillType, $fillLevel, $location );
							}
						}
					}
					break;
				case 'FS19_SteelBaleSheds.ObjectStorage':
				case 'FS19_BaleStacks.ObjectStorage':
				case 'FS19_SteelCottonSheds.ObjectStorage':
					if ($item ['farmId'] == $_SESSION ['farmId']) {
						foreach ( $item->objectStorage->storageArea as $storage) {
							$fillType = strval ( $storage ['fillType']);
							$fillLevel = 0;
							foreach ( $storage->object as $object){
								$fillLevel += intval($object ['fillLevel']);
							}
							if ( $fillLevel != 0) {	
								self::addCommodity ( $fillType, $fillLevel, $location);
							}
						}
					}
					break;

				case 'FS19_FI_FermentingSilo.FillTypeConverter':
					if ($item ['farmId'] == $_SESSION ['farmId']) {
						foreach ( $item->fillTypeConverter->outputs->children() as $storage) {
							$fillType = strval ( $storage ['lastFillType']);
							$fillLevel = intval($storage ['fillLevel']);
							
							self::addCommodity ( $fillType, $fillLevel, $location);
						}
					}
					break;
					
			}
		}
	}
	private static function loadVehicles() {
		global $gameData;
		$dataFromStore = $gameData ['vehicles'];
		foreach ( self::$xml ['vehicles'] as $vehicle ) {
			if ($vehicle ['farmId'] != self::$farmId) {
				continue;
			}
			$vehicleName = cleanFileName ( $vehicle ['filename'] );
			if (in_array ( $vehicleName, $gameData ['pallets'] )) {
				// Palette
				$location = getLocation ( $vehicle->component1 ['position'] );
				$className = 'FillablePallet';
			} else {
				// Fahrzeug
				$location = translate ( $vehicleName );
				foreach ( $dataFromStore as $basename => $storeData ) {
					if (basename ( $vehicle ['filename'] ) == $basename) {
						$name = $storeData ['name'];
						if (substr ( $name, 0, 5 ) == '$l10n') {
							$name = translate ( $name );
						}
						// $brand = strval ( $storeData ['brand'] );
						$location = $name;
						break;
					}
				}
				$className = 'isVehicle';
				$vehicleId = intval ( $vehicle ['id'] );
			}
			if (isset ( $vehicle->livestockTrailer )) {
				foreach ( $vehicle->livestockTrailer->animal as $animal ) {
					$fillType = strval ( $animal ['fillType'] );
					self::addCommodity ( $fillType, 1, $location, 'animal' );
				}
			}
			if (isset ( $vehicle->fillUnit )) {
				foreach ( $vehicle->fillUnit->unit as $unit ) {
					$fillType = strval ( $unit ['fillType'] );
					$fillLevel = intval ( $unit ['fillLevel'] );
					if (! in_array ( $fillType, self::FILLTYPES_TO_IGNORE )) {
						self::addCommodity ( $fillType, $fillLevel, $location, $className );
					}
				}
			}
			if (isset ( $vehicle->baleLoader )) {
				foreach ( $vehicle->baleLoader->bale as $bale ) {
					$fillType = cleanFileName ( $bale ['filename'] );
					$fillLevel = intval ( $bale ['fillLevel'] );
					self::addCommodity ( $fillType, $fillLevel, $location, $className );
				}
			}
		}
	}
	private static function addCommodity($fillType, $fillLevel, $location, $className = 'none', $isCombine = false) {
		if (! $fillType) {
			return false;
		}
		$l_fillType = translate ( $fillType );
		if ($className == 'isVehicle') {
			$l_location = $location;
		} elseif (substr ( $location, 0, 2 ) != '##') {
			$l_location = translate ( $location );
		} else {
			$l_location = $location;
		}
		if (! isset ( self::$commodities [$l_fillType] )) {
			$commodity = new Commodity ();
			$commodity->overall = $fillLevel;
			$commodity->i3dName = $fillType;
			$commodity->isCombine = $isCombine;
			if ($className == "animal"){
				$commodity->isAnimal = True;
			}
			else $commodity->isAnimal = False;
			$commodity->locations = array ();
		} else {
			$commodity = self::$commodities [$l_fillType];
			$commodity->overall += $fillLevel;
		}
		if (! isset ( $commodity->locations [$l_location] )) {
			$commodity->locations += array (
					$l_location => array (
							'i3dname' => $location,
							$className => 1,
							'fillLevel' => $fillLevel
					)
			);
		} else {
			if (! isset ( $commodity->locations [$l_location] [$className] )) {
				$commodity->locations [$l_location] [$className] = 1;
			} else {
				$commodity->locations [$l_location] [$className] ++;
			}
			$commodity->locations [$l_location] ['fillLevel'] += $fillLevel;
		}
		self::$commodities [$l_fillType] = $commodity;
		self::$commoditiesArray [$l_fillType] = get_object_vars ( $commodity );
		ksort ( self::$commoditiesArray [$l_fillType] ['locations'] );
	}
	private static function loadBales() {
		/*
		 * This is the old function - mostly now in analyzeItems()
		 * positions and outofmap is not ready yet
		 */
		foreach ( self::$xml ['items'] as $item ) {
			$className = strval ( $item ['className'] );
			if ($className == 'Bale' && strval ( $item ['farmId'] ) == self::$farmId) {
				$location = getLocation ( $item ['position'] );
				$fillType = cleanFileName ( $item ['filename'] );
				$fillLevel = intval ( $item ['fillLevel'] );
				self::addCommodity ( $fillType, $fillLevel, $location, $className );
				if ($location == 'outOfMap') {
					self::$commodities [translate ( $fillType )] ['outOfMap'] = true;
					// für Modal Dialog mit Edit-Vorschlag, Platzierung beim Palettenlager
					self::$outOfMapArray [] = array (
							$className,
							$fillType,
							strval ( $item ['position'] ),
							'-870 100 ' . (- 560 + sizeof ( $outOfMap ) * 2)
					);
				} else {
					self::$positions [$className] [translate ( $fillType )] [] = array (
							'name' => $className,
							'position' => explode ( ' ', $item ['position'] )
					);
				}
			}
		}
	}
}

