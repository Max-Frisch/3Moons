{block name="title" prepend}{$pageTitle}{/block}
{block name="content"}
{if isset($Winner)}
<div class="report-banner {if $Winner == 'attacker'}win{elseif $Winner == 'defender'}lose{else}draw{/if}">
	<h2>{if $Winner == 'attacker'}{$LNG.sys_attacker_won}{elseif $Winner == 'defender'}{$LNG.sys_defender_won}{else}{$LNG.sys_both_won}{/if}</h2>
	{if $Perspective !== NULL}
	<div class="report-perspective">{if $Winner == 'draw'}{$LNG.sys_report_draw_you}{elseif $YouWon}{$LNG.sys_report_you_won}{else}{$LNG.sys_report_you_lost}{/if}</div>
	{/if}
</div>
{/if}
{if isset($Info)}
<table style="width:100%">
	<tr>
		<td class="transparent" style="width:40%;font-size:22px;font-weight:bold;padding:10px 0 30px;color:{if $Raport.result == "a"}lime{elseif $Raport.result == "r"}red{else}white{/if}">{$Info.0}</td>
		<td class="transparent" style="font-size:22px;font-weight:bold;padding:10px 0 30px;">VS</td>
		<td class="transparent" style="width:40%;font-size:22px;font-weight:bold;padding:10px 0 30px;color:{if $Raport.result == "r"}lime{elseif $Raport.result == "a"}red{else}white{/if}">{$Info.1}</td>
	</tr>
</table>
{/if}
<div style="width:100%;text-align:center">
{if $Raport.mode == 1}{$LNG.sys_destruc_title}{else}{$LNG.sys_attack_title}{/if} 
{$Raport.time}:<br><br>
{foreach $Raport.rounds as $Round => $RoundInfo}
<table>
	<tr>
		{foreach $RoundInfo.attacker as $Player}
		{$PlayerInfo = $Raport.players[$Player.userID]}
		<td class="transparent">
			<table>
				<tr>
					<td>
						{$LNG.sys_attack_attacker_pos} {$PlayerInfo.name} {if isset($Info)}([XX:XX:XX]){else}([{$PlayerInfo.koords[0]}:{$PlayerInfo.koords[1]}:{$PlayerInfo.koords[2]}]{if isset($PlayerInfo.koords[3])} ({$LNG["type_planet_short_{$PlayerInfo.koords[3]}"]}){/if}){/if}<br>
						{$LNG.sys_ship_weapon} {$PlayerInfo.tech[0]}% - {$LNG.sys_ship_shield} {$PlayerInfo.tech[1]}% - {$LNG.sys_ship_armour} {$PlayerInfo.tech[2]}%
						<table width="100%">
						{if !empty($Player.ships)}
							<tr>
								<td class="transparent">{$LNG.sys_ship_type}</td>
								{foreach $Player.ships as $ShipID => $ShipData}
								<td class="transparent">{$LNG.shortNames.{$ShipID}}</td>
								{/foreach}
							</tr>
							<tr>
								<td class="transparent">{$LNG.sys_ship_count}</td>
								{foreach $Player.ships as $ShipID => $ShipData}
								<td class="transparent">{$ShipData[0]|number}</td>
								{/foreach}
							</tr>
							<tr>
								<td class="transparent">{$LNG.sys_ship_weapon}</td>
								{foreach $Player.ships as $ShipID => $ShipData}
								<td class="transparent">{$ShipData[1]|number}</td>
								{/foreach}
							</tr>
							<tr>
								<td class="transparent">{$LNG.sys_ship_shield}</td>
								{foreach $Player.ships as $ShipID => $ShipData}
								<td class="transparent">{$ShipData[2]|number}</td>
								{/foreach}
							</tr>
							<tr>
								<td class="transparent">{$LNG.sys_ship_armour}</td>
								{foreach $Player.ships as $ShipID => $ShipData}
								<td class="transparent">{$ShipData[3]|number}</td>
								{/foreach}
							</tr>
						{else}
							<tr>
								<td class="transparent">
									<br>{$LNG.sys_destroyed}<br><br>
								</td>
							</tr>
						{/if}
						</table>
					</td>
				</tr>
			</table>
		</td>
		{/foreach}
	</tr>
</table>
<br><br>
<table>
	<tr>
		{foreach $RoundInfo.defender as $Player}
		{$PlayerInfo = $Raport.players[$Player.userID]}
		<td class="transparent">
			<table>
				<tr>
					<td>
						{$LNG.sys_attack_defender_pos} {$PlayerInfo.name} {if isset($Info)}([XX:XX:XX]){else}([{$PlayerInfo.koords[0]}:{$PlayerInfo.koords[1]}:{$PlayerInfo.koords[2]}]{if isset($PlayerInfo.koords[3])} ({$LNG.type_planet_short[$PlayerInfo.koords[3]]}){/if}){/if}<br>
						{$LNG.sys_ship_weapon} {$PlayerInfo.tech[0]}% - {$LNG.sys_ship_shield} {$PlayerInfo.tech[1]}% - {$LNG.sys_ship_armour} {$PlayerInfo.tech[2]}%
						<table width="100%">
						{if !empty($Player.ships)}
							<tr>
								<td class="transparent">{$LNG.sys_ship_type}</td>
								{foreach $Player.ships as $ShipID => $ShipData}
								<td class="transparent">{$LNG.shortNames.{$ShipID}}</td>
								{/foreach}
							</tr>
							<tr>
								<td class="transparent">{$LNG.sys_ship_count}</td>
								{foreach $Player.ships as $ShipID => $ShipData}
								<td class="transparent">{$ShipData[0]|number}</td>
								{/foreach}
							</tr>
							<tr>
								<td class="transparent">{$LNG.sys_ship_weapon}</td>
								{foreach $Player.ships as $ShipID => $ShipData}
								<td class="transparent">{$ShipData[1]|number}</td>
								{/foreach}
							</tr>
							<tr>
								<td class="transparent">{$LNG.sys_ship_shield}</td>
								{foreach $Player.ships as $ShipID => $ShipData}
								<td class="transparent">{$ShipData[2]|number}</td>
								{/foreach}
							</tr>
							<tr>
								<td class="transparent">{$LNG.sys_ship_armour}</td>
								{foreach $Player.ships as $ShipID => $ShipData}
								<td class="transparent">{$ShipData[3]|number}</td>
								{/foreach}
							</tr>
						{else}
							<tr>
								<td class="transparent">
									<br>{$LNG.sys_destroyed}<br><br>
								</td>
							</tr>
						{/if}
						</table>
					</td>
				</tr>
			</table>
		</td>
		{/foreach}
	</tr>
</table>
{if !$RoundInfo@last}
{$LNG.fleet_attack_1} {$RoundInfo.info[0]|number} {$LNG.fleet_attack_2} {$RoundInfo.info[3]|number} {$LNG.damage}<br>
{$LNG.fleet_defs_1} {$RoundInfo.info[2]|number} {$LNG.fleet_defs_2} {$RoundInfo.info[1]|number} {$LNG.damage}<br><br>
{/if}
{/foreach}
<br><br>
{if $Raport.result == "a"}
{$LNG.sys_attacker_won}<br>
{$LNG.sys_stealed_ressources} {foreach $Raport.steal as $elementID => $amount}{$amount|number} {$LNG.tech.$elementID}{if ($amount@index + 2) == count($Raport.steal)} {$LNG.sys_and} {elseif !$amount@last}, {/if}{/foreach}
{elseif $Raport.result == "r"}
{$LNG.sys_defender_won}
{else}
{$LNG.sys_both_won}
{/if}
<br><br>
{$LNG.sys_attacker_lostunits} {$Raport['units'][0]|number} {$LNG.sys_units}<br>
{$LNG.sys_defender_lostunits} {$Raport['units'][1]|number} {$LNG.sys_units}<br>
{$LNG.debree_field_1} {foreach $Raport.debris as $elementID => $amount}{$amount|number} {$LNG.tech.$elementID}{if ($amount@index + 2) == count($Raport.debris)} {$LNG.sys_and} {elseif !$amount@last}, {/if}{/foreach}{$LNG.debree_field_2}<br><br>
{if $Raport.mode == 1}
	{* Destruction *}
	{if $Raport.moon.moonDestroySuccess == -1}
		{* Attack not win *}
		{$LNG.sys_destruc_stop}<br>
	{else}
		{* Attack win *}
		{sprintf($LNG.sys_destruc_lune, "{$Raport.moon.moonDestroyChance}")}<br>{$LNG.sys_destruc_mess1}
		{if $Raport.moon.moonDestroySuccess == 1}
			{* Destroy success *}
			{$LNG.sys_destruc_reussi}
		{elseif $Raport.moon.moonDestroySuccess == 0}
			{* Destroy failed *}
			{$LNG.sys_destruc_null}			
		{/if}
		<br>
		{sprintf($LNG.sys_destruc_rip, "{$Raport.moon.fleetDestroyChance}")}
		{if $Raport.moon.fleetDestroySuccess == 1}
			{* Fleet destroyed *}
			<br>{$LNG.sys_destruc_echec}
		{/if}			
	{/if}
{else}
	{* Normal Attack *}
	{$LNG.sys_moonproba} {$Raport.moon.moonChance} %<br>
	{if !empty($Raport.moon.moonName)}
		{if isset($Info)}
			{* Moon created (HoF Mode) *}
			{sprintf($LNG.sys_moonbuilt, "{$Raport.moon.moonName}", "XX", "XX", "XX")}
		{else}
			{* Moon created *}
			{sprintf($LNG.sys_moonbuilt, "{$Raport.moon.moonName}", "{$Raport.koords[0]}", "{$Raport.koords[1]}", "{$Raport.koords[2]}")}
		{/if}
	{/if}
{/if}

{$Raport.additionalInfo}
</div>

{if isset($Summary)}
<div class="report-lossbar" title="{$LNG.sys_attacker_lostunits} {$Summary.attacker.lostTotal|number} {$LNG.sys_units} / {$LNG.sys_defender_lostunits} {$Summary.defender.lostTotal|number} {$LNG.sys_units}">
	<div class="attacker" style="width:{$LossShare.attacker}%"></div>
	<div class="defender" style="width:{$LossShare.defender}%"></div>
</div>

<div class="report-summary-wrap">
	<div class="report-summary">
		<div class="report-summary-title">{$LNG.sys_attack_attacker_pos}</div>
		<table class="report-summary" style="width:100%;">
			<tr><th>{$LNG.sys_ship_type}</th><th>{$LNG.sys_report_start}</th><th>{$LNG.sys_report_lost}</th><th>{$LNG.sys_report_left}</th></tr>
			{foreach $Summary.attacker.start as $ShipID => $StartAmount}
			<tr>
				<td>{$LNG.shortNames.{$ShipID}}</td>
				<td>{$StartAmount|number}</td>
				<td>{$Summary.attacker.lost.$ShipID|number}</td>
				<td>{$Summary.attacker.left.$ShipID|number}</td>
			</tr>
			{/foreach}
		</table>
	</div>
	<div class="report-summary">
		<div class="report-summary-title">{$LNG.sys_attack_defender_pos}</div>
		<table class="report-summary" style="width:100%;">
			<tr><th>{$LNG.sys_ship_type}</th><th>{$LNG.sys_report_start}</th><th>{$LNG.sys_report_lost}</th><th>{$LNG.sys_report_left}</th></tr>
			{foreach $Summary.defender.start as $ShipID => $StartAmount}
			<tr>
				<td>{$LNG.shortNames.{$ShipID}}</td>
				<td>{$StartAmount|number}</td>
				<td>{$Summary.defender.lost.$ShipID|number}</td>
				<td>{$Summary.defender.left.$ShipID|number}</td>
			</tr>
			{/foreach}
		</table>
	</div>
</div>
{/if}
{/block} 