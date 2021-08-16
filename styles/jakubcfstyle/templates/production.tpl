<h3 class="my-3">##PRODUCTION##</h3>
{if $productions|@count}
<div class="row">
	<div class="col-3">
		<div id="accordion">
			<div class="list-group">
				{foreach $productions as $productionI3dName => $production}
				
				<button type="button" class="list-group-item d-flex justify-content-between align-items-center list-group-item-dark" onclick="location.href='index.php?page={$page}&production={$productionI3dName}'">
					<strong>{$production.name}</strong><span class="badge badge-secondary badge-pill">info</span>
				</button>
				
				{/foreach}
			</div>
		</div>
	</div>
	<div class="col-9">
		<div class="row">
			<div class="col-lg-6">
				<h4>
					{$productions.$currentProduction.name}<span class="float-right">$$$</span>
				</h4>
				<img src="{#IMAGES#}/vehicles/{$productions.$currentProduction.img}.png" class="img-fluid h-50 mx-auto d-block">
				<div class="row">
					<div class="col-6">
						<h5>##PRODUCTIVITY##</h5>
					</div>
					<div class="col-3 text-right">{$productions.$currentProduction.productivity|number_format:0:",":"."} %</div>
					<div class="col-3">
						<div class="progress">
							{$style='style="width: '|cat:$productions.$currentProduction.productivity|cat:'%"'}
							<div class="progress-bar" role="progressbar" {$style} aria-valuenow="{$productions.$currentProduction.productivity}" aria-valuemin="0" aria-valuemax="100"></div>
						</div>
					</div>
				</div>
				<div class="row mt-1">
					<div class="col-6">##PRODUC_RATE##</div>
					<div class="col-6 text-right">{} h</div>
				</div>
				<div class="row mt-1">
					<div class="col-6">##FULL_IN##</div>
					<div class="col-6 text-right">{} h</div>
				</div>
				{foreach $productions.$currentProduction.product as $productName => $product}
				<div class="row mt-1">
					<div class="col-6">{$product.name}</div>
					<div class="col-6 text-right">{$product.value|number_format:0:",":"."} {$product.unit}</div>
				</div>
				{/foreach}
			</div>
			<div class="col-lg-6">
				<h4>##PRODUCTION_OVERVIEW##</h4>
				<h5>##RAW_MATERIALS##</h5>
				{foreach $productions.$currentProduction.input as $inputName => $input}
				<div class="row mt-1">
					<div class="col-5">{$input.name}</div>
					<div class="col-4 text-right">{$input.fillLevel|number_format:0:",":"."}/{$input.capacity|number_format:0:",":"."}</div>
					<div class="col-3">
						<div class="progress">
							{$style='style="width: '|cat:$input.factor|cat:'%"'}
							<div class="progress-bar" role="progressbar" {$style} aria-valuenow="{$input.factor}" aria-valuemin="0" aria-valuemax="100"></div>
						</div>
					</div>
				</div>
				{/foreach}
				<h5 class="mt-5">##PRODUCTS##</h5>
				{foreach $productions.$currentProduction.output as $outputName => $output}
				<div class="row mt-1">
					<div class="col-5">{$output.name}</div>
					<div class="col-4 text-right">{$output.fillLevel|number_format:0:",":"."}/{$output.capacity}</div>
					<div class="col-3">
						<div class="progress">
							{$style='style="width: '|cat:$output.factor|cat:'%"'}
							<div class="progress-bar" role="progressbar" {$style} aria-valuenow="{$output.factor}" aria-valuemin="0" aria-valuemax="100"></div>
						</div>
					</div>
				</div>
				{/foreach}
			</div>
		</div>
		<div class="row">
			<div class="col">{if isset($productions.$currentProduction.product.manure)}##MANURE_HELP##{/if}</div>
		</div>

	</div>
</div>
{else}
<div class="jumbotron my-3 py-3">
	<p class="lead">##NOPRODUCTIONS##</p>
</div>
{/if}
