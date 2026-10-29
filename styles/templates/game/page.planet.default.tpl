{block name="title" prepend}{$LNG.bm_planetarium}{/block}
{block name="content"}
<div class="content_page">
	<div class="title">{$LNG.bm_planetarium}</div>
	{if $done}<div class="green" style="padding:6px;">{$LNG.bm_done}</div>{/if}
	{if $error}<div class="red" style="padding:6px;">{$error}</div>{/if}
	<p>{$LNG.bm_darkmatter}: <span class="res_921_text">{$darkmatter|number}</span></p>

	<table style="width:100%;">
		<thead>
			<tr>
				<th>{$LNG.bm_celestial}</th>
				<th>{$LNG.bm_coords}</th>
				<th>{$LNG.bm_fields}</th>
				<th>{$LNG.bm_fields_bought}</th>
				<th>{$LNG.bm_buy_fields}</th>
				<th>{$LNG.bm_teleport}</th>
			</tr>
		</thead>
		<tbody>
			{foreach $celestials as $celestial}
			<tr>
				<td>{$celestial.name}{if $celestial.isMoon} ({$LNG.fcm_moon}){/if}</td>
				<td>[{$celestial.galaxy}:{$celestial.system}:{$celestial.planet}]</td>
				<td>{$celestial.fieldsCurrent} / {$celestial.fieldsMax}</td>
				<td>{$celestial.fieldsBought}</td>
				<td>
					<form action="game.php?page=planet&amp;mode=fields" method="post">
						<input type="hidden" name="planet" value="{$celestial.id}">
						<input type="number" name="fields" value="1" min="1" max="100" style="width:60px;">
						<input type="submit" value="{$LNG.bm_buy} ({$celestial.fieldPrice|number} {$LNG.tech.921})">
					</form>
				</td>
				<td>
					{if $celestial.canMove}
					<form action="game.php?page=planet&amp;mode=relocate" method="post">
						<input type="hidden" name="planet" value="{$celestial.id}">
						<input type="number" name="galaxy" value="{$celestial.galaxy}" min="1" max="{$maxGalaxy}" style="width:50px;">
						<input type="number" name="system" value="{$celestial.system}" min="1" max="{$maxSystem}" style="width:60px;">
						<input type="number" name="position" value="{$celestial.planet}" min="1" max="{$maxPlanets}" style="width:50px;">
						<input type="submit" value="{$LNG.bm_teleport}">
					</form>
					{else}-{/if}
				</td>
			</tr>
			{/foreach}
		</tbody>
	</table>
</div>
{/block}
