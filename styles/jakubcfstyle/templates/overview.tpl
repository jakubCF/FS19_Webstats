<h3 class="my-3">##OVERVIEW##</h3>

<h4 class="my-3">##RAW_MATERIALS##</h4>
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
				{if $percent>=70}
				<tr class="{if $percent>=80 && !empty($commodities.$fillType.overall)}bg-warning{/if}">
					<td class="text-right col-1 pr-3">{$fillType}</td>
					<td class="text-right col-2 pr-3">{$fillTypeData.bestLocation}</td>
					<td class="text-right col-1 pr-3">{number_format_locale($fillTypeData.minPrice,0)}</td>
					<td class="text-right col-1 pr-3">{number_format_locale($fillTypeData.maxPrice,0)}</td>
					<td class="text-right col-1 pr-3 {if $fillTypeData.greatDemand}text-info{elseif $percent>=60}text-success{elseif $percent<=40}text-danger{/if}">{number_format_locale($fillTypeData.bestPrice,0)} {if $fillTypeData.priceTrend == 1} <i class="fas fa-caret-up text-success"></i> {elseif
						$fillTypeData.priceTrend == -1} <i class="fas fa-caret-down text-danger"></i> {else} <i class="fas fa-caret-down" style="visibility: hidden"></i> {/if}
					</td>
					<td class="text-center col-1 pr-3 text-nowrap">{$percent|number_format:0:",":"."} %</td> {if $options['farmId']>0}{if isset($commodities.$fillType) && $commodities.$fillType.overall > 0}
					<td class="text-right col-1 pr-3">{number_format_locale($commodities.$fillType.overall,0)}</td>
					{math equation="overall * bestPrice / 1000" overall=$commodities.$fillType.overall bestPrice=$fillTypeData.bestPrice assign="proceeds"}
					<td class="text-right col-1 pr-3 text-nowrap">{number_format_locale($proceeds,0)} {$currency}</td> {else}
					<td></td>
					<td></td> {/if}{/if}
				</tr>
				{/if}
				{/foreach}
			</tbody>
		</table>
	</div>
</div>

{if $stables|@count}
<h4 class="my-3">##BS_HORSES##</h4>
<div class="row">
	<div class="col-sm-12">
		<table class="table table-sm table-hover table-bordered table-striped" id="horses">
			<thead>
					<th class="text-center">##NAME##</th>
					<th class="text-center">##FITNESS##</th>
					<th class="text-center">##CLEANLINESS##</th>
					<th class="text-center">##RIDETIME##</th>
					<th class="text-center">##PROCEEDS##</th>

			</thead>
			<tbody>
				{$counthorses = 0}
				{foreach $stables as $id => $stable}
					{foreach $stable.animals as $animal}


						{if $animal.ridingTimer < 99 or $animal.dirtScale < 99}
						<tr>
							<td class="text-right col-1 pr-3">{$animal.name}</td>
							<td class="text-right col-1 pr-3">{$animal.fitnessScale} %</td>
							<td class="text-right col-1 pr-3">{$animal.dirtScale} %</td>
							<td class="text-right col-1 pr-3">{$animal.ridingTimer} %</td>
							<td class="text-right col-1 pr-3">{number_format_locale($animal.value,0)} {$currency}</td>
						</tr>
						{$counthorses =+ $counthorses}
						{/if}
					{/foreach}
				{/foreach}
			</tbody>
		</table>
		{if $counthorses == 0}
			<h5>##ALLHORSESTRAINED##<h5>
		{/if}
	</div>
</div>
{/if}

{if $productions|@count}
<h4 class="my-3">##PRODUCTION##</h4>

<div class="row">
	<div class="col-sm-12">
		<table class="table table-sm table-hover table-bordered table-striped" id="horses">
			<thead>
					<th class="text-center">##PROD_NAME##</th>
					<th class="text-center">##PROD_LINE##</th>
					<th class="text-center">##OUTPUT_NAME##</th>
					<th class="text-center">##OUTPUT_CAP##</th>
					<th class="text-center">##OUTPUT_LEVEL##</th>
					<th class="text-center">##PROGRESS##</th>
					<th class="text-center">##STATUS##</th>

			</thead>
			<tbody>
				{foreach $productions as $id => $production}
					{foreach $production.productline as $idprodline => $productline}
						{foreach $productline.output as $id2 => $output}
							<tr class="{if $output.factor>=90}bg-warning{/if}">
								<td class="text-left col-3 pr-3">{$production.name}</td>
								<td class="text-right col-1 pr-3">{$idprodline}</td>
								<td class="text-right col-1 pr-3 text-nowrap">{$output.name}</td>
								<td class="text-right col-1 pr-3">{number_format_locale($output.capacity,0)} </td>
								<td class="text-right col-1 pr-3">{number_format_locale($output.fillLevel,0)} </td>
								<td class="text-right col-1 pr-3 align-middle"><div class="progress">
								{$style='style="width: '|cat:$output.factor|cat:'%"'}
								<div class="progress-bar" role="progressbar" {$style} aria-valuenow="{$output.factor}" aria-valuemin="0" aria-valuemax="100"></div>
								</div></td>
								<td class="text-center align-middle col-1 pr-3"><i class="bi bi-circle-fill" style="{if $productline.state }color:green">{else}color:red">{/if}</i></td>
							</tr>
						{/foreach}
					{/foreach}
				{/foreach}
			</tbody>
		</table>

	</div>
</div>


{/if}
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

