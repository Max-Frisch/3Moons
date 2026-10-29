{block name="title" prepend}{$LNG.lm_arsenal}{/block}
{block name="content"}
<style type="text/css">
.ars_grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
.ars_box { padding: 8px; background: rgba(0,0,0,0.55); border: 1px solid rgba(255,255,255,0.08); }
.ars_box.owned { border-color: rgba(240,160,32,0.35); }
.ars_head { display: flex; justify-content: space-between; gap: 6px; align-items: baseline; }
.ars_name { font-weight: bold; color: #f0a020; }
.ars_group { color: #9fc5e8; font-size: 11px; }
.ars_line { color: #d8d8d8; font-size: 12px; margin-top: 4px; }
.ars_bonus { color: #8fd18f; font-weight: bold; }
.ars_next { color: #b8b8b8; }
.ars_footer { margin-top: 6px; }
.ars_absent { color: #e06666; font-weight: bold; }
.ars_notice { padding: 8px; margin: 8px 0; border-left: 3px solid #8fd18f; background: rgba(143,209,143,0.12); }
.ars_notice.error { border-left-color: #e06666; background: rgba(224,102,102,0.12); }
.ars_stats { float: right; font-weight: normal; font-size: 12px; }
</style>

<div class="content_page" style="width: 97%;">
	<div class="title">
		{$LNG.lm_arsenal}
		<span class="ars_stats">
			{$LNG.ars_found}: <b>{$arsenalFound}</b> &nbsp; {$LNG.ars_activated}: <b>{$arsenalActivated}</b>
		</span>
	</div>

	{if $arsenalNotice}
	<div class="ars_notice{if $arsenalNotice.type == 'error'} error{/if}">
		{if $arsenalNotice.type == 'ok'}
			{if $arsenalNotice.success}
				{sprintf($LNG.ars_notice_success, $arsenalNotice.name, $arsenalNotice.level, $arsenalNotice.bonus|number:2)}
			{else}
				{sprintf($LNG.ars_notice_failure, $arsenalNotice.name, $arsenalNotice.chance * 100)}
			{/if}
		{else}
			{if $arsenalNotice.reason == 'items'}{$LNG.ars_err_items}
			{else}{$LNG.ars_err_unknown}{/if}
		{/if}
	</div>
	{/if}

	<div class="ars_grid">
		{foreach $arsenalUpgrades as $upgrade}
		<div class="ars_box{if $upgrade.level > 0 || $upgrade.available > 0} owned{/if}">
			<div class="ars_head">
				<span class="ars_name">{$upgrade.name}</span>
				<span class="ars_group">{$upgrade.group}</span>
			</div>
			<div class="ars_line">
				{$LNG.ars_bonus} <span class="ars_bonus">+{$upgrade.bonus|number:2}%</span>
				<span class="ars_next">(+{$upgrade.nextBonus|number:2})</span>
				&nbsp; {$LNG.ars_level} <b>{$upgrade.level}</b>
			</div>
			<div class="ars_line">
				{$LNG.ars_available} <b>{$upgrade.available}</b>
			</div>
			<div class="ars_footer">
				{if $upgrade.canActivate}
				<form action="game.php?page=arsenal" method="post">
					<input type="hidden" name="mode" value="send">
					<input type="hidden" name="code" value="{$upgrade.code}">
					{if $upgrade.key}<input type="hidden" name="greid" value="{$upgrade.key}">{/if}
					<button type="submit" class="build_submit construct_button" style="width: auto;">{$LNG.ars_activate}</button>
				</form>
				{else}
				<span class="ars_absent">{$LNG.ars_absent}</span>
				{/if}
			</div>
		</div>
		{/foreach}
	</div>
</div>
{/block}
