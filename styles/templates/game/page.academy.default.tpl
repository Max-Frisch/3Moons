{block name="title" prepend}{$LNG.lm_academy}{/block}
{block name="content"}
<style type="text/css">
.acad_branches { display: flex; gap: 12px; align-items: flex-start; }
.acad_branch { flex: 1 1 33%; min-width: 0; }
.acad_branch_title { font-size: 16px; font-weight: bold; color: #f0a020; padding: 6px 8px; margin-bottom: 6px; border-bottom: 1px solid rgba(240,160,32,0.4); }
.acad_skill { padding: 8px; margin-bottom: 8px; background: rgba(0,0,0,0.55); border: 1px solid rgba(255,255,255,0.08); }
.acad_skill.maxed { border-color: rgba(143,209,143,0.4); }
.acad_head { display: flex; gap: 6px; align-items: baseline; }
.acad_tier { display: inline-block; min-width: 26px; text-align: center; color: #9fc5e8; font-size: 11px; border: 1px solid rgba(159,197,232,0.5); border-radius: 3px; padding: 0 3px; }
.acad_name { font-weight: bold; color: #f0a020; flex: 1 1 auto; }
.acad_level { color: #8fd18f; font-weight: bold; }
.acad_effect { color: #d8d8d8; margin: 4px 0; font-size: 12px; }
.acad_current { color: #8fd18f; font-size: 12px; }
.acad_prereq { color: #b8b8b8; font-size: 11px; margin-top: 4px; }
.acad_footer { margin-top: 6px; }
.acad_form { display: flex; gap: 5px; align-items: center; flex-wrap: wrap; font-size: 12px; }
.acad_form input[type=number] { width: 54px; }
.acad_reset { margin-top: 8px; padding: 8px; background: rgba(0,0,0,0.35); border-top: 1px solid rgba(255,255,255,0.08); font-size: 12px; }
.acad_reset form { display: inline-block; margin: 4px 4px 0 0; }
.acad_notice { padding: 8px; margin: 8px 0; border-left: 3px solid #8fd18f; background: rgba(143,209,143,0.12); }
.acad_notice.error { border-left-color: #e06666; background: rgba(224,102,102,0.12); }
</style>

<div class="content_page" style="width: 97%;">
	<div class="title">
		{$LNG.lm_academy}
		<span style="float: right; font-weight: normal;">
			{$LNG.acad_points}: <span style="color: #f0a020; font-weight: bold;">{$academyPoints}</span>
			/ {$academyPointsSpent}
		</span>
	</div>

	{if $academyNotice}
	<div class="acad_notice{if $academyNotice.type == 'error'} error{/if}">
		{if $academyNotice.type == 'ok'}
			{if $academyNotice.partial}
				{sprintf($LNG.acad_notice_partial, $academyNotice.level, $academyNotice.requested, $academyNotice.spent)}
			{else}
				{sprintf($LNG.acad_notice_upgraded, $academyNotice.level, $academyNotice.spent)}
			{/if}
		{elseif $academyNotice.type == 'reset'}
			{sprintf($LNG.acad_notice_reset, $academyNotice.percent, $academyNotice.refunded)}
		{else}
			{if $academyNotice.reason == 'prereq'}{$LNG.acad_err_prereq}
			{elseif $academyNotice.reason == 'points'}{$LNG.acad_err_points}
			{elseif $academyNotice.reason == 'level'}{$LNG.acad_err_level}
			{elseif $academyNotice.reason == 'empty'}{$LNG.acad_err_empty}
			{elseif $academyNotice.reason == 'antimatter'}{$LNG.acad_err_antimatter}
			{elseif $academyNotice.reason == 'darkmatter'}{$LNG.acad_err_darkmatter}
			{else}{$LNG.acad_err_unknown}{/if}
		{/if}
	</div>
	{/if}

	<div class="acad_branches">
		{foreach $academyBranches as $branchID => $branch}
		<div class="acad_branch">
			<div class="acad_branch_title">{$branch.title}</div>

			{foreach $branch.skills as $skill}
			<div class="acad_skill{if $skill.maxed} maxed{/if}">
				<div class="acad_head">
					<span class="acad_tier">{$skill.tierRoman}</span>
					<span class="acad_name">{$skill.name}</span>
					<span class="acad_level">{$skill.level}/{$skill.maxLevel}</span>
				</div>
				<div class="acad_effect">{$skill.effectText}</div>
				{if $skill.currentEffect}<div class="acad_current">{$LNG.acad_current}: {$skill.currentEffect}</div>{/if}
				{if $skill.prereq}
				<div class="acad_prereq">
					{foreach $skill.prereq as $line}{$line}{if !$line@last}<br>{/if}{/foreach}
				</div>
				{/if}
				<div class="acad_footer">
					{if $skill.maxed}
					<span style="color: #8fd18f;">{$LNG.acad_maxed}</span>
					{else}
					<form action="game.php?page=academy" method="post" class="acad_form">
						<input type="hidden" name="action" value="up">
						<input type="hidden" name="skill" value="{$skill.code}">
						<span>{$LNG.acad_next_cost} <b style="color: #f0a020;">{$skill.cost|number}</b></span>
						<input type="number" name="target" min="{$skill.nextLevel}" max="{$skill.maxLevel}" value="{$skill.nextLevel}">
						<button type="submit" class="build_submit construct_button" style="width: auto;" {if !$skill.buyable}disabled{/if}>{$LNG.acad_upgrade}</button>
					</form>
					{/if}
				</div>
			</div>
			{/foreach}

			<div class="acad_reset">
				<div>{$LNG.acad_reset}</div>
				<form action="game.php?page=academy" method="post" onsubmit="return confirm('{$LNG.acad_reset_confirm}');">
					<input type="hidden" name="action" value="reset">
					<input type="hidden" name="branch" value="{$branchID}">
					<input type="hidden" name="percent" value="100">
					<button type="submit" class="build_submit construct_button" style="width: auto;">{sprintf($LNG.acad_reset_full, $resetFullCost)}</button>
				</form>
				<form action="game.php?page=academy" method="post" onsubmit="return confirm('{$LNG.acad_reset_confirm}');">
					<input type="hidden" name="action" value="reset">
					<input type="hidden" name="branch" value="{$branchID}">
					<input type="hidden" name="percent" value="75">
					<button type="submit" class="build_submit construct_button" style="width: auto;">{sprintf($LNG.acad_reset_partial, $resetPartialCost)}</button>
				</form>
				<form action="game.php?page=academy" method="post" onsubmit="return confirm('{$LNG.acad_reset_confirm}');">
					<input type="hidden" name="action" value="reset">
					<input type="hidden" name="branch" value="{$branchID}">
					<input type="hidden" name="percent" value="50">
					<button type="submit" class="build_submit construct_button" style="width: auto;">{$LNG.acad_reset_free}</button>
				</form>
			</div>
		</div>
		{/foreach}
	</div>
</div>
{/block}
