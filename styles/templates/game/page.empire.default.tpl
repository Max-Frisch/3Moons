{block name="title" prepend}{$LNG.lm_empire}{/block}
{block name="content"}

<div class="content_page">
	<div class="title">
		{$LNG.lv_imperium_title}
	</div>

	<div style="padding:6px;">
		<button type="button" class="empire-filter" data-filter="all">{$LNG.lv_all}</button>
		<button type="button" class="empire-filter" data-filter="building">{$LNG.lv_buildings}</button>
		<button type="button" class="empire-filter" data-filter="fleet">{$LNG.lv_ships}</button>
		<button type="button" class="empire-filter" data-filter="defense">{$LNG.lv_defenses}</button>
	</div>

	<div style="overflow-x:auto;">
		<table id="empireTable">
			<tbody>
				<tr data-section="all">
					<td style="width:100px">{$LNG.lv_planet}</td>
					<td style="width:100px;font-size: 50px;">&Sigma;</td>
					{foreach $planetList.image as $planetID => $image}
					<td style="width:100px"><a href="game.php?page=overview&amp;cp={$planetID}"><img width="80" height="80" border="0" src="{$dpath}planeten/small/s_{$image}.jpg"></a></td>
					{/foreach}
				</tr>
				<tr data-section="all">
					<td>{$LNG.lv_name}</td>
					<td>{$LNG.lv_total}</td>
					{foreach $planetList.name as $planetID => $name}
						<td><a href="game.php?page=overview&amp;cp={$planetID}">{$name}</a></td>
					{/foreach}
				</tr>
				<tr data-section="all">
					<td>{$LNG.lv_coords}</td>
					<td>-</td>
					{foreach $planetList.coords as $planetID => $coords}
						<td><a href="game.php?page=galaxy&amp;galaxy={$coords.galaxy}&amp;system={$coords.system}">[{$coords.galaxy}:{$coords.system}:{$coords.planet}]</a></td>
					{/foreach}
				</tr>
				<tr data-section="all">
					<td>{$LNG.lv_fields}</td>
					<td>-</td>
					{foreach $planetList.field as $planetID => $field}
						<td>{$field.current} / {$field.max}</td>
					{/foreach}
				</tr>
				<tr data-section="all">
					<th class="title" colspan="{$colspan}">{$LNG.lv_resources}</th>
				</tr>
				{foreach $planetList.resource as $elementID => $resourceArray}
				<tr data-section="all">
					<td>{$LNG.tech.$elementID}</td>
					<td class="res_{$elementID}_text">{array_sum($resourceArray)|number}</td>
					{foreach $resourceArray as $planetID => $resource}
						<td class="res_{$elementID}_text">{$resource|number}</td>
					{/foreach}
				</tr>
				{/foreach}
				<tr data-section="all">
					<th class="title" colspan="{$colspan}">{$LNG.lv_production}</th>
				</tr>
				{foreach $planetList.production as $elementID => $productionArray}
				<tr data-section="all">
					<td>{$LNG.tech.$elementID}</td>
					<td class="res_{$elementID}_text">{array_sum($productionArray)|number}</td>
					{foreach $productionArray as $planetID => $production}
						<td class="res_{$elementID}_text">{$production|number}</td>
					{/foreach}
				</tr>
				{/foreach}

				<tr data-section="building">
					<th class="title" colspan="{$colspan}">{$LNG.lv_buildings}</th>
				</tr>
				{foreach $planetList.build as $elementID => $buildArray}
				<tr data-section="building">
					<td>{$LNG.tech.$elementID}</td>
					<td>{array_sum($buildArray)|number}</td>
					{foreach $buildArray as $planetID => $build}
						<td>{$build|number}</td>
					{/foreach}
				</tr>
				{/foreach}

				<tr data-section="fleet">
					<th class="title" colspan="{$colspan}">{$LNG.lv_ships}</th>
				</tr>
				{foreach $planetList.fleet as $elementID => $fleetArray}
				<tr data-section="fleet">
					<td>{$LNG.tech.$elementID}</td>
					<td>{array_sum($fleetArray)|number}</td>
					{foreach $fleetArray as $planetID => $fleet}
						<td>{$fleet|number}</td>
					{/foreach}
				</tr>
				{/foreach}

				<tr data-section="defense">
					<th class="title" colspan="{$colspan}">{$LNG.lv_defenses}</th>
				</tr>
				{foreach $planetList.defense as $elementID => $fleetArray}
				<tr data-section="defense">
					<td>{$LNG.tech.$elementID}</td>
					<td>{array_sum($fleetArray)|number}</td>
					{foreach $fleetArray as $planetID => $fleet}
						<td>{$fleet|number}</td>
					{/foreach}
				</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
</div>
{/block}
{block name="script" append}
<script>
(function () {
	var buttons = document.querySelectorAll('.empire-filter');
	var rows = document.querySelectorAll('#empireTable tr[data-section]');
	for (var i = 0; i < buttons.length; i++) {
		buttons[i].addEventListener('click', function () {
			var filter = this.getAttribute('data-filter');
			for (var j = 0; j < rows.length; j++) {
				var section = rows[j].getAttribute('data-section');
				rows[j].style.display = (filter === 'all' || section === 'all' || section === filter) ? '' : 'none';
			}
		});
	}
})();
</script>
{/block}
