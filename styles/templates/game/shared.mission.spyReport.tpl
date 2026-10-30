<div class="spyRaport">
	<div class="report-banner draw">
		<h2>{$title}</h2>
	</div>
	{foreach $spyData as $Class => $elementIDs}
	<div class="spyRaportContainer">
		<div class="spyRaportContainerHead spyRaportContainerHeadClass{$Class}">{$LNG.tech.$Class}</div>
		<div class="spyRaportGrid">
			{foreach $elementIDs as $elementID => $amount}
			<div class="spyRaportStat">
				<span class="spyRaportName">{$LNG.tech.$elementID}</span>
				<span class="spyRaportValue">{$amount|number}</span>
			</div>
			{/foreach}
		</div>
	</div>
	{/foreach}
	<div class="spyRaportFooter">
		<a class="msg-link" href="game.php?page=fleetTable&amp;galaxy={$targetPlanet.galaxy}&amp;system={$targetPlanet.system}&amp;planet={$targetPlanet.planet}&amp;planettype={$targetPlanet.planet_type}&amp;target_mission=1">{$LNG.type_mission_1}</a>
		<br>{if $targetChance >= $spyChance}{$LNG.sys_mess_spy_destroyed}{else}{sprintf($LNG.sys_mess_spy_lostproba, $targetChance)}{/if}
		{if $isBattleSim}<br><a class="msg-link" href="game.php?page=battleSimulator{foreach $spyData as $Class => $elementIDs}{foreach $elementIDs as $elementID => $amount}&amp;im[{$elementID}]={$amount}{/foreach}{/foreach}">{$LNG.fl_simulate}</a>{/if}
	</div>
</div>
