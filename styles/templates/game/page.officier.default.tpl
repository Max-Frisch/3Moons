{block name="title" prepend}{$LNG.lm_officiers}{/block}
{block name="content"}

<div class="content_page" style="width: 95%;">
	{if !empty($governatorList)}
	<div class="title">
		{$LNG.of_governators}
		<span style="float: right; font-weight: normal;">{$LNG.ach_governor_ap} <span style="color: #f0a020; font-weight: bold;">{$achievementPoints}</span> / {$achievementPointsSpent}</span>
	</div>

	{foreach $governatorList as $ID => $Element}
		<div class="main_construct">
			<div class="block_construct">
				<div class="title" style="margin: 5px 0 0 -5px; text-align: left;">
					<a href="#" onclick="return Dialog.info({$ID})"><img src="{$dpath}gebaeude/{$ID}.gif" alt="{$LNG.tech.{$ID}}" width="20" height="20"> {$LNG.tech.{$ID}} [{$LNG.of_lvl} {$Element.level}/{$Element.maxLevel}]</a>
					{if $Element.timeLeft > 0}
					<span style="float: right; font-size: 12px; color: #8fd18f;">{$LNG.ach_governor_active}: {$Element.timeLeft|time}</span>
					{/if}
				</div>

				<div class="block_construct_desc">
					<div class="block_construct_desc_list">
						<div style="margin-bottom: 8px;">
							{foreach $Element.bonus as $Bonus}
							<span style="color: lime;">{if $Bonus.pct < 0}{$Bonus.pct}{else}+{$Bonus.pct}{/if}%</span> {$Bonus.name}{if !$Bonus@last}&nbsp;{/if}
							{/foreach}
						</div>

						<div style="margin-bottom: 8px;">
							{$LNG.of_darkmatter}: <span style="color: #9fc5e8; font-weight: bold;">{$Element.dmPerDay|number}</span>
						</div>

						<div>
							{$LNG.ach_governor_ap} <span style="color: #f0a020; font-weight: bold;">{$Element.apCost|number}</span>
							{if $Element.level >= $Element.maxLevel} - {$LNG.bd_maxlevel}{/if}
						</div>
					</div>

					<div style="display: flex; gap: 6px; align-items: center; margin-top: 8px;">
						<form action="game.php?page=officier" method="post" style="display: flex; gap: 4px; align-items: center; flex: 1 1 auto;">
							<input type="hidden" name="action" value="hire">
							<input type="hidden" name="id" value="{$ID}">
							<span>{$LNG.ach_governor_days}</span>
							<input type="number" name="days" min="1" max="7" value="1" style="width: 50px;">
							<button type="submit" class="build_submit construct_button" style="width: auto; flex: 1 1 auto;"{if !$Element.dmBuyable} disabled{/if}>{$LNG.ach_governor_hire}</button>
						</form>
						<form action="game.php?page=officier" method="post" style="flex: 1 1 auto;">
							<input type="hidden" name="action" value="improve">
							<input type="hidden" name="id" value="{$ID}">
							<button type="submit" class="build_submit construct_button" style="width: 100%; margin-top: 0;"{if !$Element.apBuyable || $Element.level >= $Element.maxLevel} disabled{/if}>{$LNG.ach_improve}</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	{/foreach}
	<div class="clear"></div>
	{/if}

	{if $officierList}
	<div class="title" {if !empty($governatorList)}style="margin: 5px 0 -5px 0;"{/if}>
		{$LNG.of_offi}
	</div>

	{foreach $officierList as $ID => $Element}
		<div class="main_construct">
			
			<div class="block_construct">
				<div class="title" style="margin: 5px 0 0 -5px; text-align: left;">
					<a href="#" onclick="return Dialog.info({$ID})"><img src="{$dpath}gebaeude/{$ID}.jpg" alt="{$LNG.tech.{$ID}}" width="20" height="20"> {$LNG.tech.{$ID}} - {$LNG.of_lvl} {$Element.level}/{$Element.maxLevel}</a>
				</div>

				<div class="block_construct_desc">
					<div class="block_construct_desc_list">
						{foreach $Element.costResources as $RessID => $RessAmount}
							<div style="margin-bottom: 10px;">
								<img src="{$dpath}images/{$RessID}.gif" alt=""> <b><span {if $Element.costOverflow[$RessID] == 0}class="res_{$RessID}_text"{/if} style="color:{if $Element.costOverflow[$RessID] == 0}{else}red{/if}">{$RessAmount|number}</span></b>
							</div>
						{/foreach}

						<div style="margin-bottom: 10px;">
							{foreach $Element.elementBonus as $BonusName => $Bonus}{if $Bonus@iteration % 3 === 1}<p>{/if}{if $Bonus[0] < 0}-{else}+{/if}{if $Bonus[1] == 0}{abs($Bonus[0] * 100)}%{else}{$Bonus[0]}{/if} {$LNG.bonus.$BonusName}{if $Bonus@iteration % 3 === 0 || $Bonus@last}</p>{else}&nbsp;{/if}{/foreach}
						</div>

					</div>

					<div>
						{if $Element.maxLevel <= $Element.level}
						<div class="construct_button_lost">
							<span style="color:red">{$LNG.bd_maxlevel}</span>
						</div>
						{elseif $Element.buyable}
							<form action="game.php?page=officier" method="post" class="build_form">
								<input type="hidden" name="id" value="{$ID}">
								<button type="submit" class="build_submit construct_button">{$LNG.of_recruit}</button>
							</form>
						{else}
						<div class="construct_button_lost">
							<span style="color:red">{$LNG.of_recruit}</span>
						</div>
						{/if}
					</div>
				</div>
			</div>
		</div>
	{/foreach}
	<div class="clear"></div>
	{/if}

</div>

{/block}
{block name="script"}
<script src="scripts/game/officier.js"></script>
{/block}
