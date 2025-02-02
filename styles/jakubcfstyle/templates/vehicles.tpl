{if $subPage == 'vehicles'}
<h3 class="my-3">##VEH_VEHICLES##</h3>
<div class="row">
	<div class="col-sm-12">
		<table class="table table-sm table-hover display table-bordered table-striped" id="vehicles">
			<thead>
					<!-- <th class="text-center">##VEH_BRAND##</th> -->
					<th class="text-center">##VEH_NAME##</th>
					<th class="text-center">##VEH_CATEGORY##</th>
					<th class="text-center">##VEH_AGE##</th>
					<th class="text-center">##VEH_WEAR##</th>
					<th class="text-center">##VEH_OTIME##</th>
					<th class="text-center">##VEH_PRICE##</th>
					<th class="text-center">##VEH_RESALE##</th>
					<th class="text-center">##VEH_LPDAY##</th>
					<th class="text-center">##VEH_LPHOUR##</th>
					<th class="text-center">##VEH_LCOST##</th>
			</thead>
			<tbody>
				{foreach $vehicles as $vehicleId => $vehicle}
					{if $vehicle.category == "pallets"}
						{continue}
					{/if}
					<tr class="{if $vehicle.propertyState==2 && ($vehicle.leasingCost/$vehicle.price) > 0.6}bg-danger{/if}">
						<!-- <td>{$vehicle.brand}</td> -->
						<td><div class="hover-title">{$vehicle.brand} {$vehicle.name}</div>{if $vehicle.img != ""}<div class="hover-img"><img src="{#IMAGES#}/vehicles/{$vehicle.img}.png"><span>{$vehicle.name}</span></div>{/if}</td>
						<td>{translate($vehicle.category)}</td>
						<td class="text-right pr-3">{$vehicle.age}</td>
						<td class="text-right pr-3">{$vehicle.wear|number_format:0}&#8239;%</td>
						<td data-order="{$vehicle.operatingTime|number_format:0:" ,":"."}" class="text-right pr-3">{$vehicle.operatingTimeString}</td>
						<td class="text-right pr-3">{$vehicle.price|number_format_locale:0}</td>
						<td data-order="{if $vehicle.propertyState==1}{$vehicle.resale}{else}0{/if}" class="text-right pr-3">{if $vehicle.propertyState==1}{number_format_locale($vehicle.resale,0)}{elseif $vehicle.propertyState==3}Mission{/if}</td>
						<td class="text-right pr-3">{if $vehicle.propertyState==2}{number_format_locale($vehicle.dayLeasingCost,0)}{/if}</td>
						<td class="text-right pr-3">{if $vehicle.propertyState==2}{number_format_locale($vehicle.leasingCostPerHour,0)}{/if}</td>
						<td class="text-right pr-3">{if $vehicle.propertyState==2}{number_format_locale($vehicle.leasingCost,0)}{/if}</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
		<script>
		var h = window.innerHeight; 			//Height of the HTML document
		var c = 285; 							// Sum of the heights of navbar, footer, headings, etc.  
		var th = parseInt((h-c)/h*100) + 'vh';	// Height for table
		var rw = parseInt((h - c) / 30);		// Rows when paging is activated
		$(document).ready(function() {
		    var table = $('#vehicles').DataTable( {
		    	//"pageLength": rw,
		    	scrollY:        th,
				scrollX:        false, 				
        		scrollCollapse: true,
       			paging:         false,
		    	stateSave:		true,
				"columnDefs": [ {
						"targets": [3,4,5,6,7,8,9],
						"type": "num-fmt",
						} ],
				order: [[3, "asc"]],
		    	"dom":	"<'row'<'col-sm-12'tr>>", // cut from beginn: <'row'<'col-sm-6'><'col-sm-6'f>> cut from end: <'row'<'col-sm-5'i><'col-sm-7'p>>		
		    	"language": {
		    		"decimal": ",",
		            "thousands": ".",
		            "url": "./language/{$smarty.session.language}/dataTables.lang"
		    	}
		    } );
		} );
		</script>
	</div>
</div>
{elseif $subPage == 'buildings'}
<h3 class="my-3">##VEH_BUILDINGS##</h3>
<div class="row">
	<div class="col-sm-12">
		<table class="table table-sm table-hover display table-bordered table-striped" id="buildings">
			<thead>
				<tr>
					<th class="text-center">##VEH_BNAME##</th>
					<th class="text-center">##VEH_AGE##</th>
					<th class="text-center">##VEH_PRICE##</th>
					<th class="text-center">##VEH_RESALE##</th>
				</tr>
			</thead>
			<tbody>
				{foreach $buildings as $buildingId => $building}
				<tr>
					<td><div class="hover-title">{$building.name}</div>{if $building.img != ""}<div class="hover-img"><img src="{#IMAGES#}/vehicles/{$building.img}.png"><span>{$building.name}</span></div>{/if}</td>
					<td class="text-right pr-3">{$building.age}</td>
					<td class="text-right pr-3">{$building.price|number_format_locale:0}</td>
					<td data-order="{$building.resale}" class="text-right pr-3">{$building.resale|number_format_locale:0}</td>
				</tr>
				{/foreach}
			</tbody>
		</table>
		<script>
		var h = window.innerHeight; 			//Height of the HTML document
		var c = 285; 							// Sum of the heights of navbar, footer, headings, etc.  
		var th = parseInt((h-c)/h*100) + 'vh';	// Height for table
		var rw = parseInt((h - c) / 30);		// Rows when paging is activated
		$(document).ready(function() {
		    var table = $('#buildings').DataTable( {
		    	//"pageLength": rw,
		    	scrollY:        th,
        		scrollCollapse: true,
       			paging:         false,
		    	stateSave:		true,
				"columnDefs": [ {
						"targets": [1,2,3],
						"type": "num-fmt",
						} ],
				order: [[1, "desc"]],
		    	"dom":	"<'row'<'col-sm-12'tr>>", // cut from beginn: <'row'<'col-sm-6'><'col-sm-6'f>> cut from end: <'row'<'col-sm-5'i><'col-sm-7'p>>		
		    	"language": {
		    		"decimal": ",",
		            "thousands": ".",
		            "url": "./language/{$smarty.session.language}/dataTables.lang"
		    	}
		    } );
		} );
		</script>
	</div>
</div>
{/if}
