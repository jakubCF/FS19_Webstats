<h3 class="my-3">##PRICES1000##</h3>
{$mode = GetParam('subPage','G','bestPrices')}
{if $mode == 'allPrices'}
<!-- Alternative ingame like price overview. Looks not good. -->
<div class="row">
	<div class="col-sm-12">
		<table class="table  table-hover table-bordered table-striped" id="allPrices">
			<thead>
				<tr>
					<th class="">##SELLTRIGGER##</th> {foreach $prices as $fillType => $fillTypeData}
					<th class="text-center"><img src="{#IMAGES#}/icons/{$fillTypeData.i3dName|strtolower}.png"><span style="font-size:80%">{$fillType}</span></th> {/foreach}
				</tr>
			</thead>
			<tbody>
				{foreach $sellingPoints as $location => $i3dName}
				<tr>
					<td class="text-nowrap">
						<strong>{$location}</strong>
					</td> 
					{foreach $prices as $fillType => $fillTypeData}
						{if isset($fillTypeData.locations.$location)}
							{math equation="round(100 / max * current)" max=$fillTypeData.maxPrice-$fillTypeData.minPrice+0.0001 current=$fillTypeData.locations.$location.price-$fillTypeData.locations.$location.minPrice+0.0001 assign="percent"}
							{$dataorder="data-order='{$fillTypeData.locations.$location.price}'"}
							{if $fillTypeData.locations.$location.greatDemand}
								{$class="text-info"}
							{elseif $percent>=60}
								{$class="text-success"}
							{elseif $percent<=40}
								{$class="text-danger"}
							{else}
								{$class=""}
							{/if}
							{if $fillTypeData.locations.$location.priceTrend == 1}
								{$trend='<i class="fas fa-caret-up text-success"></i>'}
							{elseif $fillTypeData.locations.$location.priceTrend == -1}
								{$trend='<i class="fas fa-caret-down text-danger"></i>'}
							{else}
								{$trend='<i class="fas fa-caret-down" style="visibility: hidden"></i>'}
							{/if}
							{$value=$fillTypeData.locations.$location.price|floor|number_format:0:",":"."}
							
						{else}
							{$dataorder=""}{$class=""}{$value=""}{$trend=""}
						{/if}
						<td {$dataorder} class="text-nowrap {$class} pr-2">{$trend}<span class="float-right">{$value}</span></td>
					{/foreach}
				</tr>
				{/foreach}
			</tbody>
			<tfoot>
				<tr>
					<th>##STOCKS##</th> {foreach $prices as $fillType => $fillTypeData}
					<th class="text-right pr-2">{if isset($commodities.$fillType)}{$commodities.$fillType.overall|number_format:0:",":"."}{/if}</th> {/foreach}
				</tr>
			</tfoot>
		</table>
		<script>{foreach $prices as $price}{if $price@first}{$cols = '['|cat:$price@iteration}{else}{$cols = $cols|cat:','|cat:$price@iteration}{/if}{/foreach}{$cols = $cols|cat:']'}
		var cols = {$cols};
		var h = window.innerHeight; //Height of the HTML document
{if $options.hideFooter}
		var c = 320; // Sum of the heights of navbar, footer, headings, etc.
{else}
		var c = 400; // Sum of the heights of navbar, footer, headings, etc.
{/if} 
		var th = parseInt((h-c)/h*100) + 'vh'; // Height for table 
		var rw = parseInt((h - c) / 30); // Rows when paging is activated
		$(document).ready(function() {
			$('#allPrices').DataTable( {
				"fixedColumns": true,
				columnDefs: [
			        { "width": "150px", "targets": [0] },       
			        { "width": "45px", "targets": cols }
			      ],
				"bFilter": false,
		    	"paging": false,		    	
		    	"autoWidth": true,
		    	"info": false,
		    	"scrollX": true,
		    	scrollY: th,
		        "scrollCollapse": true,
		        "language": {
		    		"url": "./language/{$smarty.session.language}/dataTables.lang"
		    	}
		    } );
		} );
		</script>
		<p class="text-center text-warning">##PRICE_INFO##</p>
	</div>
</div>
{else}
<div class="row">
	<div class="col-sm-12">
		<table class="table table-sm table-hover table-bordered table-striped" id="bestPrices">
			<thead>
					<th class="text-center">##STOCK##</th>
					<th class="text-center">##SELLTRIGGER##</th>
					<th class="text-center">##MIN_PRICE##</th>
					<th class="text-center">##MAX_PRICE##</th>
					<th class="text-center">##BEST_PRICE##</th>
					<th class="text-center">##PERCENT##</th> {if $options['farmId']>0}
					<th class="text-center">##STOCKS##</th>
					<th class="text-center">##PROCEEDS##</th> {/if}
			</thead>
			<tbody>
				{foreach $prices as $fillType => $fillTypeData} {math equation="round(100 / max * current)" max=$fillTypeData.maxPrice-$fillTypeData.minPrice+0.0001 current=$fillTypeData.bestPrice-$fillTypeData.minPrice+0.0001 assign="percent"}
				<tr class="{if $percent>=80 && !empty($commodities.$fillType.overall)}bg-warning{/if}">
					<td>{$fillType}</td>
					<td>{$fillTypeData.bestLocation}</td>
					<td class="text-right col-1 pr-3">{number_format_locale($fillTypeData.minPrice,0)}</td>
					<td class="text-right col-1 pr-3">{number_format_locale($fillTypeData.maxPrice,0)}</td>
					<td class="text-right col-1 pr-3 {if $fillTypeData.greatDemand}text-info{elseif $percent>=60}text-success{elseif $percent<=40}text-danger{/if}">{number_format_locale($fillTypeData.bestPrice,0)} {if $fillTypeData.priceTrend == 1} <i class="fas fa-caret-up text-success"></i> {elseif
						$fillTypeData.priceTrend == -1} <i class="fas fa-caret-down text-danger"></i> {else} <i class="fas fa-caret-down" style="visibility: hidden"></i> {/if}
					</td>
					<td class="text-center text-nowrap">{$percent|number_format:0:",":"."} %</td> {if $options['farmId']>0}{if isset($commodities.$fillType) && $commodities.$fillType.overall > 0}
					<td class="text-right col-1 pr-3">{number_format_locale($commodities.$fillType.overall,0)}</td>
					{math equation="overall * bestPrice / 1000" overall=$commodities.$fillType.overall bestPrice=$fillTypeData.bestPrice assign="proceeds"}
					<td class="text-right col-1 pr-3">{number_format_locale($proceeds,0)} {$currency}</td> {else}
					<td></td>
					<td></td> {/if}{/if}
				</tr>
				{/foreach}
			</tbody>
		</table>
		<script>
			var h = window.innerHeight; //Height of the HTML document
			{if $options.hideFooter}
			var c = 230; // Sum of the heights of navbar, footer, headings, etc.
			{else}
			var c = 265; // Sum of the heights of navbar, footer, headings, etc.
			{/if} 
			var th = parseInt((h-c)/h*100) + 'vh'; // Height for table 
			var rw = parseInt((h - c) / 30); // Rows when paging is activated
			$(document).ready(function() { 
				var table = $('#bestPrices').DataTable( { 
					//"pageLength": rw, 
					scrollY: th,
					scrollX: "98%",					
					scrollCollapse: true, 
					paging:	false, 
					stateSave: true,
					"columnDefs": [ {
						"targets": [5,6,7],
						"type": "html-num-fmt",
						} ],
					order: [[6, "desc"]], 
					"dom": "<'row'<'col-sm-12'tr>>", 
					"language": {  
						"url": "./language/{$smarty.session.language}/dataTables.lang"
					}
				} ); 
			} );
		</script>
	</div>
</div>
{/if}
