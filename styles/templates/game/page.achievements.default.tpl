{block name="title" prepend}{$LNG.lm_achievements}{/block}
{block name="content"}
<style type="text/css">
.ach_wrap { display: flex; gap: 12px; align-items: flex-start; }
.ach_menu { width: 220px; flex: 0 0 220px; }
.ach_menu a { display: block; padding: 8px 10px; margin-bottom: 4px; background: rgba(0,0,0,0.45); border-left: 3px solid transparent; }
.ach_menu a.active { border-left-color: #f0a020; color: #f0a020; background: rgba(240,160,32,0.12); }
.ach_list { flex: 1 1 auto; min-width: 0; }
.ach_item { display: flex; gap: 10px; padding: 10px; margin-bottom: 8px; background: rgba(0,0,0,0.55); border: 1px solid rgba(255,255,255,0.08); }
.ach_icon { width: 48px; flex: 0 0 48px; text-align: center; font-size: 26px; color: #f0a020; }
.ach_icon img { max-width: 48px; max-height: 48px; }
.ach_body { flex: 1 1 auto; }
.ach_name { font-weight: bold; color: #f0a020; }
.ach_desc { color: #8fd18f; margin: 3px 0; }
.ach_req { color: #d8d8d8; margin-top: 4px; }
.ach_req .label { color: #9fc5e8; }
.ach_meta { margin-top: 5px; color: #b8b8b8; font-size: 12px; }
.ach_side { width: 160px; flex: 0 0 160px; text-align: right; }
.ach_points { display: inline-block; padding: 2px 8px; border-radius: 3px; background: #21618c; color: #fff; font-weight: bold; }
.ach_next { margin-top: 6px; color: #b8b8b8; font-size: 12px; }
</style>

<div class="content_page" style="width: 95%;">
	<div class="title">
		{$LNG.lm_achievements}
		<span style="float: right;">{$LNG.ach_points} <span style="color: #f0a020; font-weight: bold;">{$achievementPoints}</span></span>
	</div>

	<div class="ach_wrap" style="margin-top: 10px;">
		<div class="ach_menu">
			{foreach $groupList as $group}
			<a href="{$group.url}"{if $group.active} class="active"{/if}>{$group.title}</a>
			{/foreach}
			<div style="margin-top: 10px; color: #b8b8b8; font-size: 12px;">
				{$LNG.ach_antimatter}: <span style="color: #8fd18f; font-weight: bold;">{$antimatter}</span>
			</div>
		</div>

		<div class="ach_list">
			{foreach $achievementList as $Element}
			<div class="ach_item">
				<div class="ach_icon">
					{if $Element.icon > 0}
					<img src="{$dpath}gebaeude/{$Element.icon}.gif" alt="">
					{else}
					<i class="fas fa-star"></i>
					{/if}
				</div>
				<div class="ach_body">
					<div class="ach_name">{$Element.name} {$Element.level} {$LNG.ach_level}</div>
					{if $Element.description}<div class="ach_desc">{$Element.description}</div>{/if}
					<div class="ach_req">
						<span class="label">{$LNG.ach_requirements}</span><br>
						{foreach $Element.requirements as $line}{$line}<br>{/foreach}
					</div>
					<div class="ach_meta">{$LNG.ach_current}: {$Element.progress}</div>
				</div>
				<div class="ach_side">
					<span class="ach_points"><i class="fas fa-star"></i> {$Element.earned}</span>
					<div class="ach_next">
						{$LNG.ach_next_bonus}<br>
						&bull; {$Element.nextAntimatter} {$LNG.ach_antimatter}<br>
						&bull; {$Element.nextPoints} {$LNG.ach_points}
					</div>
				</div>
			</div>
			{foreachelse}
			<div class="ach_item">{$LNG.ach_empty}</div>
			{/foreach}
		</div>
	</div>
</div>
{/block}
