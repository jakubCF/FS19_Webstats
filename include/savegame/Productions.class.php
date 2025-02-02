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
class Production {
	private $name;
	private $i3dName;
	private $brand;
	private $img;
	private $category = '';
	private $age;
	private $lifetime = 600;
	private $dailykeepup;
	private $price;
	private $resale;

	public static $productions = array ();

	public static function extractXML($xml, $farmId, $gameData) {
		global $gameData;
		foreach ( $xml ['items'] as $item ) {
			$dataFromStore = $gameData['placeables'];
			if ($item ['farmId'] != $farmId || strval ( $item ['className'] ) != 'FS19_GlobalCompany.GC_ProductionFactoryPlaceable') {
				continue;
			}
			$filename = cleanFileName ( $item ['filename'] );
			$production = new Production ();
			$production->name = translate ( $filename );
			foreach ( $dataFromStore as $basename => $storeData ) {
				if (basename ( $item ['filename'] ) == $basename) {
					$name = $storeData ['name'];
					if (substr ( $name, 0, 5 ) == '$l10n') {
						$name = translate ( $name );
					}
					$production->name = $name;
					$production->img = strval( $storeData ['img']);
					$production->brand = strval ( $storeData ['brand'] );
					$production->lifetime = intval ( $storeData ['lifetime'] );
					$production->dailykeepup = intval ( $storeData ['dailykeepup'] );
					$production->category = strval ( $storeData ['category'] );
					
					foreach ($storeData['inputProducts'] as $id => $inputProduct){
						//error_log($id);
						$production->input[$id]["name"] = translate(strtoupper($inputProduct["name"]));
						$production->input[$id]["capacity"] = $inputProduct["capacity"];
						$production->input[$id]["fillType"] = $inputProduct["fillType"];
						if (isset($item->productionFactory->inputProducts->inputProduct)){
							foreach ($item->productionFactory->inputProducts->inputProduct as $currentlevel){
								if ($currentlevel["name"] == $inputProduct["name"]){
									$production->input[$id]["factor"] = ($currentlevel["fillLevel"]/$inputProduct["capacity"])*100;
									$production->input[$id]["fillLevel"] = get_bool($currentlevel["fillLevel"]);
								}
							}
						}
					}
					if(isset($storeData["outputProducts"])){
						//error_log("outputs");
						foreach($storeData["outputProducts"] as $id => $outputProduct){
							$production->output[$id]["name"] = translate(strtoupper($outputProduct["name"]));
							$production->output[$id]["capacity"] = $outputProduct["capacity"];
							$production->output[$id]["fillType"] = $outputProduct["fillType"];
							if (isset($item->productionFactory->outputProducts->outputProduct)){
								foreach ($item->productionFactory->outputProducts->outputProduct as $currentlevel){
									if ($currentlevel["name"] == $outputProduct["name"]){
										$production->output[$id]["factor"] = ($currentlevel["fillLevel"]/$outputProduct["capacity"])*100;
										$production->output[$id]["fillLevel"] = get_bool($currentlevel["fillLevel"]);
									}
								}
							}
						}
					}
					foreach ($storeData['productLine'] as $id => $productline){
						//error_log($id);
						$production->productline[$id]["id"] = $productline["id"];
						$production->productline[$id]["outputPerHour"] = $productline["outputPerHour"];
						
						if (isset($item->productionFactory->productLines->productLine)){
							foreach ($item->productionFactory->productLines->productLine as $itemProdLine){
								if (intval($itemProdLine["lineId"]) == intval($productline["id"])){
									$production->productline[$id]["state"] = get_bool($itemProdLine["state"]);

									foreach ($productline['Input'] as $prodinid => $prodLineInput){

										$production->productline[$id]["input"][$prodinid]["name"] = translate(get_bool($prodLineInput["name"]));
										$production->productline[$id]["input"][$prodinid]["percent"] = get_bool($prodLineInput["percent"]);

									}

									foreach ($productline['Output'] as $prodinid => $prodLineOutput){

										$production->productline[$id]["output"][$prodinid]["name"] = translate(get_bool($prodLineOutput["name"]));
										$production->productline[$id]["output"][$prodinid]["percent"] = get_bool($prodLineOutput["percent"]);

										if (isset($item->productionFactory->outputProducts->outputProduct)){
											foreach ($item->productionFactory->outputProducts->outputProduct as $currentlevel){
												if ( strtolower($currentlevel["name"]) == strtolower($prodLineOutput["name"])){
													$production->productline[$id]["output"][$prodinid]["fillLevel"] = get_bool($currentlevel["fillLevel"]);
													break;
												}
											}
										}
										if(isset($storeData["outputProducts"])){
											//error_log("outputs");
											foreach($storeData["outputProducts"] as $outputProduct){
												
												if(strtolower($production->productline[$id]["output"][$prodinid]["name"]) == strtolower(translate($outputProduct["name"]))){
													$production->productline[$id]["output"][$prodinid]["capacity"] = $outputProduct["capacity"];
													break;
												}
											}
										}
										$production->productline[$id]["output"][$prodinid]["factor"] = ($production->productline[$id]["output"][$prodinid]["fillLevel"]/$production->productline[$id]["output"][$prodinid]["capacity"])*100;

									}
								}
							}

						}
					}
					break;
				}
			}
			$production->age = intval ( $item ['age'] );
			$production->price = intval ( $item ['price'] );

			self::$productions [] = get_object_vars ( $production );
		}
	}
	public static function getAllProductions(){
		return self::$productions;
	}

}