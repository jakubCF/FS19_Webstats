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

include ('./include/savegame/Prices.class.php');
Price::extractXML ( $savegame );
$prices = Price::getAllPrices ();

include ('./include/savegame/Commodities.class.php');
Commodity::loadCommodities ( $savegame::$xml );
$commodities = Commodity::getAllCommodities ();

// Hide prices with no stock
foreach ( $prices as $fillType => $price ) {
	if (! isset ( $commodities [$fillType] ) || $commodities [$fillType] ['overall'] == 0) {
		unset ( $prices [$fillType] );
	}
}

$smarty->assign ( 'options', $options ['general'] );
$smarty->assign ( 'prices', $prices );
$smarty->assign ( 'commodities', $commodities );
$smarty->assign ( 'sellingPoints', Price::getSellStations () );


include ('./include/savegame/Animals.class.php');
Animals::loadStables ( $savegame::$xml );
$stables = Animals::getStables ();

foreach ($stables as $id => $stable) {
	if (! $stable["forHorses"]){
		unset($stables[$id]);
	}
}

$smarty->assign ( 'stables', $stables );	

include ('./include/savegame/Productions.class.php');
Production::extractXML ( $savegame::$xml, $options ['general'] ['farmId'], $gameData );
$productions = Production::getAllProductions ();

$smarty->assign ( 'productions', $productions );
