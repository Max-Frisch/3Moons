{block name="title" prepend}{$LNG.bm_collider}{/block}
{block name="content"}
<div class="content_page">
	<div class="title">{$LNG.bm_collider}</div>
	{if $done}<div class="green" style="padding:6px;">{$LNG.bm_done}</div>{/if}
	{if $error}<div class="red" style="padding:6px;">{$error}</div>{/if}
	<div style="display:flex;gap:18px;align-items:flex-start;">
		<img src="{$dpath}gebaeude/69.gif" alt="{$LNG.bm_collider}" style="width:200px;">
		<div>
			<p>{$LNG.bm_collider_desc}</p>
			<p>{$LNG.bm_level}: <strong>{$level} / {$maxLevel}</strong></p>
			<p>{$LNG.bm_collider_production}: <strong class="res_921_text">{$production|number}</strong> / h</p>
			{if $level < $maxLevel}
			<p>{$LNG.bm_collider_next}: <strong class="res_921_text">{$nextProduction|number}</strong> / h</p>
			<p>{$LNG.bm_collider_cost}: <strong>{$cost|number}</strong> {$LNG.bm_antimatter} ({$LNG.bm_antimatter_have}: {$antimatter|number})</p>
			<form action="game.php?page=kollaider&amp;mode=build" method="post">
				<input type="submit" value="{$LNG.bm_buy}"{if $antimatter < $cost} disabled{/if}>
			</form>
			{/if}
		</div>
	</div>
</div>
{/block}
