<?php

/**
 *  2Moons 
 *   by Jan-Otto Kröpke 2009-2016
 *
 * For the full copyright and license information, please view the LICENSE
 *
 * @package 2Moons
 * @author Jan-Otto Kröpke <slaver7@gmail.com>
 * @copyright 2009 Lucky
 * @copyright 2016 Jan-Otto Kröpke <slaver7@gmail.com>
 * @licence MIT
 * @version 1.8.0
 * @link https://github.com/jkroepke/2Moons
 */

class MissionCaseExpedition extends MissionFunctions implements Mission
{
	function __construct($fleet)
	{
		$this->_fleet	= $fleet;
	}
	
	function TargetEvent()
	{
		$this->setState(FLEET_HOLD);
		$this->SaveFleet();
	}
	
	function EndStayEvent()
	{
		global $pricelist, $reslist, $resource;
		$LNG	= $this->getLanguage(NULL, $this->_fleet['fleet_owner']);

		$config	= Config::get($this->_fleet['fleet_universe']);

		$fleetArray		= FleetFunctions::unserialize($this->_fleet['fleet_array']);
		$fleetPoints	= Expedition::fleetPoints($fleetArray);
		$fleetCapacity	= 0;

		foreach ($fleetArray as $shipId => $shipAmount)
		{
			$fleetCapacity += $shipAmount * $pricelist[$shipId]['capacity'];
		}

		$fleetCapacity -= $this->_fleet['fleet_resource_metal'] + $this->_fleet['fleet_resource_crystal']
			+ $this->_fleet['fleet_resource_deuterium'] + $this->_fleet['fleet_resource_darkmatter'];
		$fleetCapacity = max(0, $fleetCapacity);

		$outcome = Expedition::rollOutcome(Expedition::rand());

		switch($outcome)
		{
			case Expedition::OUTCOME_RESOURCES:
				$loot = Expedition::loot($fleetCapacity, Expedition::rand());
				$this->UpdateFleet('fleet_resource_metal', $this->_fleet['fleet_resource_metal'] + $loot[901]);
				$this->UpdateFleet('fleet_resource_crystal', $this->_fleet['fleet_resource_crystal'] + $loot[902]);
				$this->UpdateFleet('fleet_resource_deuterium', $this->_fleet['fleet_resource_deuterium'] + $loot[903]);
				$Message = $LNG['sys_expe_found_ress_'.Expedition::flavour($outcome, Expedition::rand())];
			break;

			case Expedition::OUTCOME_BLACKHOLE_LOOT:
				$loot = Expedition::blackHoleLoot($fleetCapacity, Expedition::rand(), Expedition::rand());
				$this->UpdateFleet('fleet_resource_metal', $this->_fleet['fleet_resource_metal'] + $loot[901]);
				$this->UpdateFleet('fleet_resource_crystal', $this->_fleet['fleet_resource_crystal'] + $loot[902]);
				$this->UpdateFleet('fleet_resource_deuterium', $this->_fleet['fleet_resource_deuterium'] + $loot[903]);
				$Message = $LNG['sys_expe_blackholl_loot'];
			break;

			case Expedition::OUTCOME_STARDUST:
				Database::get()->update(
					"UPDATE %%USERS%% SET stardust = stardust + 1 WHERE id = :userId;",
					array(':userId' => $this->_fleet['fleet_owner'])
				);
				$Message = $LNG['sys_expe_stardust_'.Expedition::flavour($outcome, Expedition::rand())];
			break;

			case Expedition::OUTCOME_DARKMATTER:
				$size = Expedition::darkMatter($fleetPoints, Expedition::rand());
				$this->UpdateFleet('fleet_resource_darkmatter', $this->_fleet['fleet_resource_darkmatter'] + $size);
				$Message = $LNG['sys_expe_found_dm_'.Expedition::flavour($outcome, Expedition::rand())];
			break;

			case Expedition::OUTCOME_SHIPS:
				$Found = Expedition::recovery($fleetPoints, array(
					Expedition::rand(), Expedition::rand(), Expedition::rand(), Expedition::rand(),
					Expedition::rand(), Expedition::rand(), Expedition::rand()
				));

				$FoundShipMess = '';
				foreach($Found as $ID => $Count)
				{
					$fleetArray[$ID] = (isset($fleetArray[$ID]) ? $fleetArray[$ID] : 0) + $Count;
					$FoundShipMess .= '<br>'.$LNG['tech'][$ID].': '.pretty_number($Count);
				}
				if(empty($Found))
				{
					$FoundShipMess = '<br><br>'.$LNG['sys_expe_found_ships_nothing'];
				}

				$NewFleetArray = '';
				foreach($fleetArray as $ID => $Count)
				{
					$NewFleetArray .= $ID.','.floatToString($Count).';';
				}
				$this->UpdateFleet('fleet_array', substr($NewFleetArray, 0, -1));
				$this->UpdateFleet('fleet_amount', array_sum($fleetArray));

				$upgradeCode = Arsenal::rollDrop(Arsenal::tier($fleetPoints));
				if($upgradeCode > 0)
				{
					$upgradeDef = Arsenal::getUpgrade($upgradeCode);
					if($upgradeDef !== NULL && Arsenal::addItems($this->_fleet['fleet_owner'], $upgradeCode, 1))
					{
						$FoundShipMess .= '<br><br>'.sprintf($LNG['arsenal_expo_found'], $upgradeDef['name']);
					}
				}

				$Message = $LNG['sys_expe_found_ships_'.Expedition::flavour($outcome, Expedition::rand())].$FoundShipMess;
			break;

			case Expedition::OUTCOME_NOTHING:
				$Message = $LNG['sys_expe_nothing_'.Expedition::flavour($outcome, Expedition::rand())];
			break;

			case Expedition::OUTCOME_DELAY:
				$adjust = 600 + mt_rand(0, 6600);
				$this->UpdateFleet('fleet_end_time', $this->_fleet['fleet_end_time'] + $adjust);
				$Message = $LNG['sys_expe_time_slow_'.Expedition::flavour($outcome, Expedition::rand())];
			break;

			case Expedition::OUTCOME_FAST:
				$adjust = 600 + mt_rand(0, 5400);
				$this->UpdateFleet('fleet_end_time', max($this->_fleet['fleet_start_time'] + 60, $this->_fleet['fleet_end_time'] - $adjust));
				$Message = $LNG['sys_expe_time_fast_'.Expedition::flavour($outcome, Expedition::rand())];
			break;

			case Expedition::OUTCOME_BLACKHOLE:
				$this->KillFleet();
				$Message = $LNG['sys_expe_lost_fleet_'.Expedition::flavour($outcome, Expedition::rand())];
			break;

			case Expedition::OUTCOME_COMBAT:
			default:
				$messageHTML	= <<<HTML
<div class="raportMessage">
<table>
<tr>
<td colspan="2"><a href="CombatReport.php?raport=%s" target="_blank"><span class="%s">%s %s (%s)</span></a></td>
</tr>
<tr>
<td>%s</td><td><span class="%s">%s: %s</span>&nbsp;<span class="%s">%s: %s</span></td>
</tr>
<tr>
<td>%s</td><td><span>%s:&nbsp;<span class="raportSteal element901">%s</span>&nbsp;</span><span>%s:&nbsp;<span class="raportSteal element902">%s</span>&nbsp;</span><span>%s:&nbsp;<span class="raportSteal element903">%s</span></span></td>
</tr>
<tr>
<td>%s</td><td><span>%s:&nbsp;<span class="raportDebris element901">%s</span>&nbsp;</span><span>%s:&nbsp;<span class="raportDebris element902">%s</span></span></td>
</tr>
</table>
</div>
HTML;
				$messageHTML	= str_replace(array("\n", "\t", "\r"), "", $messageHTML);

				$npc = Expedition::rollNPC(Expedition::rand());

				$sql = 'SELECT * FROM %%USERS%% WHERE id = :userId;';
				$senderData = Database::get()->selectSingle($sql, array(
					':userId' => $this->_fleet['fleet_owner']
				));

				$mirrored = Expedition::mirroredResearchBonus(
					$senderData['military_tech'], $senderData['defence_tech'], $senderData['shield_tech']
				);
				list($targetFleetData, $enemyBonus) = Expedition::enemyComposition(
					$fleetArray, $npc, Expedition::rand(), Expedition::rand(), $mirrored
				);
				$targetFleetData = array_filter($targetFleetData);

				if($npc === Expedition::NPC_ALIENS)
				{
					$targetName = $LNG['sys_expe_attackname_2'];
					$Message = $LNG['sys_expe_attack_2_3_2'];
				}
				else
				{
					$targetName = $LNG['sys_expe_attackname_1'];
					$Message = $LNG['sys_expe_attack_1_3_2'];
				}

				$targetData = array(
					'id' => 0,
					'username' => $targetName,
					'military_tech' => 0,
					'defence_tech' => 0,
					'shield_tech' => 0,
					'rpg_amiral' => 0,
					'dm_defensive' => 0,
					'dm_attack' => 0
				);

				$fleetID = $this->_fleet['fleet_id'];
				$fleetAttack[$fleetID]['fleetDetail'] = $this->_fleet;
				$fleetAttack[$fleetID]['player'] = $senderData;
				$fleetAttack[$fleetID]['player']['factor'] = getFactors($senderData, 'attack', $this->_fleet['fleet_start_time']);
				$fleetAttack[$fleetID]['unit'] = $fleetArray;

				$fleetDefend = array();
				$fleetDefend[0]['fleetDetail'] = array(
					'fleet_start_galaxy' => $this->_fleet['fleet_end_galaxy'],
					'fleet_start_system' => $this->_fleet['fleet_end_system'],
					'fleet_start_planet' => $this->_fleet['fleet_end_planet'],
					'fleet_start_type' => 1,
					'fleet_end_galaxy' => $this->_fleet['fleet_end_galaxy'],
					'fleet_end_system' => $this->_fleet['fleet_end_system'],
					'fleet_end_planet' => $this->_fleet['fleet_end_planet'],
					'fleet_end_type' => 1,
					'fleet_resource_metal' => 0,
					'fleet_resource_crystal' => 0,
					'fleet_resource_deuterium' => 0
				);

				$bonusList = BuildFunctions::getBonusList();
				$npcFactor = ArrayUtil::combineArrayWithSingleElement($bonusList, 0);
				$npcFactor['Attack'] = $enemyBonus;
				$npcFactor['Shield'] = $enemyBonus;
				$npcFactor['Defensive'] = $enemyBonus;

				$fleetDefend[0]['player'] = $targetData;
				$fleetDefend[0]['player']['factor'] = $npcFactor;
				$fleetDefend[0]['unit'] = $targetFleetData;

				require_once 'includes/classes/missions/functions/calculateAttack.php';
				$combatResult = calculateAttack($fleetAttack, $fleetDefend, $config->Fleet_Cdr, $config->Defs_Cdr);

				$combatXpAward = 0;
				if($combatResult['won'] === 'a')
				{
					$combatXpAward = Progression::awardCombatXP(
						$this->_fleet['fleet_owner'],
						Progression::lostResources(isset($combatResult['unitLost']['defender']) ? $combatResult['unitLost']['defender'] : array())
					);

					$upgradeCode = Arsenal::rollDrop(Arsenal::tier($fleetPoints));
					if($upgradeCode > 0)
					{
						$upgradeDef = Arsenal::getUpgrade($upgradeCode);
						if($upgradeDef !== NULL && Arsenal::addItems($this->_fleet['fleet_owner'], $upgradeCode, 1))
						{
							$Message .= '<br><br>'.sprintf($LNG['arsenal_expo_found'], $upgradeDef['name']);
						}
					}
				}

				$fleetArray = '';
				$totalCount = 0;
				$fleetAttack[$fleetID]['unit'] = array_filter($fleetAttack[$fleetID]['unit']);
				foreach ($fleetAttack[$fleetID]['unit'] as $element => $amount)
				{
					$fleetArray .= $element.','.$amount.';';
					$totalCount += $amount;
				}

				if ($totalCount <= 0)
				{
					$this->KillFleet();
				}
				else
				{
					$this->UpdateFleet('fleet_array', substr($fleetArray, 0, -1));
					$this->UpdateFleet('fleet_amount', $totalCount);
				}

				require_once('includes/classes/missions/functions/GenerateReport.php');
				$debris = array(901 => 0, 902 => 0);
				$stealResource = array(901 => 0, 902 => 0, 903 => 0);
				$reportInfo = array(
					'thisFleet' => $this->_fleet,
					'debris' => $debris,
					'stealResource' => $stealResource,
					'moonChance' => 0,
					'moonDestroy' => false,
					'moonName' => NULL,
					'moonDestroyChance' => NULL,
					'moonDestroySuccess' => NULL,
					'fleetDestroyChance' => NULL,
					'fleetDestroySuccess' => NULL,
				);
				$reportData = GenerateReport($combatResult, $reportInfo);
				$reportID = md5(uniqid('', true).TIMESTAMP);

				$sql = "INSERT INTO %%RW%% SET
				rid = :reportId,
				raport = :reportData,
				time = :time,
				attacker = :attacker;";
				Database::get()->insert($sql, array(
					':reportId' => $reportID,
					':reportData' => serialize($reportData),
					':time' => $this->_fleet['fleet_start_time'],
					':attacker' => $this->_fleet['fleet_owner'],
				));

				switch($combatResult['won'])
				{
					case "a":
						$attackClass = 'raportWin';
						$defendClass = 'raportLose';
					break;
					case "r":
						$attackClass = 'raportLose';
						$defendClass = 'raportWin';
					break;
					default:
						$attackClass = 'raportDraw';
						$defendClass = 'raportDraw';
					break;
				}

				$message = sprintf($messageHTML,
					$reportID,
					$attackClass,
					$LNG['sys_mess_attack_report'],
					sprintf(
						$LNG['sys_adress_planet'],
						$this->_fleet['fleet_end_galaxy'],
						$this->_fleet['fleet_end_system'],
						$this->_fleet['fleet_end_planet']
					),
					$LNG['type_planet_short_'.$this->_fleet['fleet_end_type']],
					$LNG['sys_lost'],
					$attackClass,
					$LNG['sys_attack_attacker_pos'], pretty_number($combatResult['unitLost']['attacker']),
					$defendClass,
					$LNG['sys_attack_defender_pos'], pretty_number($combatResult['unitLost']['defender']),
					$LNG['sys_gain'],
					$LNG['tech'][901], pretty_number($stealResource[901]),
					$LNG['tech'][902], pretty_number($stealResource[902]),
					$LNG['tech'][903], pretty_number($stealResource[903]),
					$LNG['sys_debris'],
					$LNG['tech'][901], pretty_number($debris[901]),
					$LNG['tech'][902], pretty_number($debris[902])
				);

				if ($combatXpAward > 0) {
					$message .= '<br><br>'.sprintf($LNG['sys_combat_xp'], pretty_number($combatXpAward));
				}

				PlayerUtil::sendMessage($this->_fleet['fleet_owner'], 0, $LNG['sys_mess_tower'], 3,
					$LNG['sys_mess_attack_report'], $message, $this->_fleet['fleet_end_stay']);
			break;
		}

		PlayerUtil::sendMessage($this->_fleet['fleet_owner'], 0, $LNG['sys_mess_tower'], 15,
			$LNG['sys_expe_report'], $Message, $this->_fleet['fleet_end_stay'], NULL, 1, $this->_fleet['fleet_universe']);

		$this->setState(FLEET_RETURN);
		$this->SaveFleet();
	}

	function ReturnEvent()
	{
		$LNG		= $this->getLanguage(NULL, $this->_fleet['fleet_owner']);
		$Message 	= sprintf(
			$LNG['sys_expe_back_home'],
			$LNG['tech'][901], pretty_number($this->_fleet['fleet_resource_metal']),
			$LNG['tech'][902], pretty_number($this->_fleet['fleet_resource_crystal']),
			$LNG['tech'][903], pretty_number($this->_fleet['fleet_resource_deuterium']),
			$LNG['tech'][921], pretty_number($this->_fleet['fleet_resource_darkmatter'])
		);

		PlayerUtil::sendMessage($this->_fleet['fleet_owner'], 0, $LNG['sys_mess_tower'], 4, $LNG['sys_mess_fleetback'],
			$Message, $this->_fleet['fleet_end_time'], NULL, 1, $this->_fleet['fleet_universe']);

		$this->RestoreFleet();
	}
}
