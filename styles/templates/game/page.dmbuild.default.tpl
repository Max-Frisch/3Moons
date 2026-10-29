{block name="title" prepend}{$LNG.bm_buildings_market}{/block}
{block name="content"}
<div class="content_page">
	<div class="title">{$LNG.bm_buildings_market}</div>
	{if $done}<div class="green" style="padding:6px;">{$LNG.bm_done}</div>{/if}
	{if $error}<div class="red" style="padding:6px;">{$error}</div>{/if}
	<p>{$LNG.bm_darkmatter}: <span class="res_921_text">{$darkmatter|number}</span> &bull; {$LNG.bm_field_free}: {$fieldFree}</p>
	<table style="width:100%;">
		<thead>
			<tr>
				<th>{$LNG.bm_building}</th>
				<th>{$LNG.bm_level}</th>
				<th>{$LNG.bm_next_level}</th>
				<th>{$LNG.bm_price}</th>
				<th>{$LNG.bm_darkmatter}</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{foreach $list as $element => $info}
			<tr>
				<td>{$info.name}</td>
				<td>{$info.level}</td>
				<td>{$info.next}</td>
				<td>
					{foreach $info.cost as $resourceId => $amount}
						<span class="res_{$resourceId}_text">{$amount|number}</span>
					{/foreach}
				</td>
				<td class="res_921_text">{$info.darkmatter|number}</td>
				<td>
					<form action="game.php?page=dmbuild&amp;mode=buy" method="post">
						<input type="hidden" name="element" value="{$element}">
						<input type="submit" value="{$LNG.bm_buy}"{if $info.noFields} disabled{/if}>
					</form>
				</td>
			</tr>
			{/foreach}
		</tbody>
	</table>
</div>
{/block}
