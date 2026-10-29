{block name="title" prepend}{$LNG.lm_bonus}{/block}
{block name="content"}

<div class="content_page">
	<div class="title">
		{$LNG.lm_bonus}
	</div>

	{if $bonusNotice}
	<div class="bonus_notice{if $bonusNotice.type == 'error'} error{/if}">
		{if $bonusNotice.type == 'ok'}
			{sprintf($LNG.pr_bonus_received, $bonusNotice.reward.darkmatter|number, $bonusNotice.reward.experience_pct|number:0, $bonusNotice.reward.academy_points)}
		{elseif $bonusNotice.reason == 'cooldown'}
			{$LNG.pr_bonus_not_ready} {$bonusNotice.ready_seconds|time}
		{else}
			{$LNG.pr_bonus_not_ready}
		{/if}
	</div>
	{/if}

	<div class="bonus_panel">
		<h3>{$LNG.pr_bonus_title}</h3>
		<p>{$LNG.pr_bonus_desc}</p>

		<table class="bonus_table">
			<tr>
				<td>{$LNG.pr_bonus_level}</td>
				<td><b>{$peacefulLevel}</b> ({$peacefulProgress}%)</td>
			</tr>
			<tr>
				<td>{$LNG.pr_bonus_next}</td>
				<td>{$peacefulNext}</td>
			</tr>
			<tr>
				<td>{$LNG.pr_bonus_reward}</td>
				<td>
					{$LNG.pr_darkmatter}: <b>{$bonusReward.darkmatter|number}</b> &nbsp;
					{$LNG.pr_experience}: <b>{$bonusReward.experience_pct|number:0}%</b> &nbsp;
					{$LNG.pr_experience_points}: <b>{$bonusReward.academy_points}</b>
				</td>
			</tr>
			<tr>
				<td>{$LNG.pr_bonus_cooldown}</td>
				<td>{$bonusCooldown|time}{if $bonusMultiplier > 1} &nbsp; {$LNG.pr_bonus_multiplier}: x{$bonusMultiplier|number:0}{/if}</td>
			</tr>
		</table>

		<form action="game.php?page=bonus" method="post" class="bonus_form">
			<input type="hidden" name="action" value="claim">
			<input type="submit" value="{if $bonusReady}{$LNG.pr_bonus_ready}{else}{sprintf($LNG.pr_bonus_wait, $bonusReadySeconds)}{/if}"{if !$bonusReady} disabled{/if}>
		</form>
	</div>

	<div class="bonus_panel">
		<h3>{$LNG.pr_bonus_bonuses}</h3>
		<ul class="bonus_list">
			<li>{$LNG.pr_bonus_mines}: <b>+{$peacefulBonuses.mines}%</b></li>
			<li>{$LNG.pr_bonus_studies}: <b>+{$peacefulBonuses.studies}%</b></li>
			<li>{$LNG.pr_bonus_energy}: <b>+{$peacefulBonuses.energy}%</b></li>
			<li>{$LNG.pr_bonus_fleet_slots}: <b>+{$peacefulBonuses.fleet_slots}</b></li>
			<li>{$LNG.pr_bonus_fields_planet}: <b>+{$peacefulBonuses.fields_planet}</b></li>
			<li>{$LNG.pr_bonus_fields_moon}: <b>+{$peacefulBonuses.fields_moon}</b></li>
			<li>{$LNG.pr_bonus_factories}: <b>{$peacefulBonuses.small_factory} / {$peacefulBonuses.medium_factory} / {$peacefulBonuses.large_factory}</b></li>
		</ul>
	</div>

	{if $premiumBonuses}
	<div class="bonus_panel">
		<h3>{$LNG.pr_bonus_premium}</h3>
		<ul class="bonus_list">
			{foreach $premiumBonuses as $premium}
			<li>{$premium.name}: <b>{$premium.value|number:0}</b> &nbsp; ({$premium.remaining})</li>
			{/foreach}
		</ul>
	</div>
	{/if}
</div>

<style>
.bonus_notice { padding: 8px; margin: 8px 0; border-left: 3px solid #8fd18f; background: rgba(143,209,143,0.12); }
.bonus_notice.error { border-left-color: #e06666; background: rgba(224,102,102,0.12); }
.bonus_panel { margin: 12px 0; padding: 12px; border: 1px solid rgba(255,255,255,0.12); border-radius: 6px; }
.bonus_panel h3 { margin-top: 0; }
.bonus_table { width: 100%; border-collapse: collapse; }
.bonus_table td { padding: 4px 6px; }
.bonus_table td:first-child { width: 260px; color: #999; }
.bonus_list { margin: 0; padding-left: 18px; }
.bonus_list li { margin: 3px 0; }
.bonus_form { margin-top: 10px; }
.bonus_form input[type=submit] { padding: 6px 18px; }
</style>

{/block}
