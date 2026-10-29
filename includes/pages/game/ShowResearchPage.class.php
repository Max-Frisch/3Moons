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

require_once('AbstractGamePage.class.php');

class ShowResearchPage extends AbstractGamePage
{
	public static $requireModule = MODULE_RESEARCH;

	function __construct() 
	{
		parent::__construct();
	}
	
	private function CheckLabSettingsInQueue()
	{
		global $PLANET;
		if (empty($PLANET['b_building']))
			return true;

		$CurrentQueue = unserialize($PLANET['b_building_id'] ?? '');
		if (!is_array($CurrentQueue))
			return true;

		foreach($CurrentQueue as $ListIDArray) {
			if(!is_array($ListIDArray))
				continue;
			if(($ListIDArray[0] ?? 0) == 6 || ($ListIDArray[0] ?? 0) == 31)
				return false;
		}

		return true;
	}
	
	private function CancelBuildingFromQueue()
	{
		global $PLANET, $USER, $resource;
		$CurrentQueue  = unserialize($USER['b_tech_queue']);
		if (empty($CurrentQueue) || empty($USER['b_tech']))
		{
			$USER['b_tech_queue']	= '';
			$USER['b_tech_planet']	= 0;
			$USER['b_tech_id']		= 0;
			$USER['b_tech']			= 0;

			return false;
		}

		$db = Database::get();

		$elementId		= (int) $CurrentQueue[0][0];
		$BuildLevel		= (int) $CurrentQueue[0][1];
		$costResources	= BuildFunctions::getElementPrice($USER, $PLANET, $elementId, false, $BuildLevel);
		
		if($PLANET['id'] == $USER['b_tech_planet'])
		{
			if(isset($costResources[901])) { $PLANET[$resource[901]]	+= $costResources[901]; }
			if(isset($costResources[902])) { $PLANET[$resource[902]]	+= $costResources[902]; }
			if(isset($costResources[903])) { $PLANET[$resource[903]]	+= $costResources[903]; }
		}
		else
		{
			$params = array('techPlanet' => $USER['b_tech_planet']);
			$sql = "UPDATE %%PLANETS%% SET ";
			if(isset($costResources[901])) {
				$sql	.= $resource[901]." = ".$resource[901]." + :".$resource[901].", ";
				$params[':'.$resource[901]] = $costResources[901];
			}
			if(isset($costResources[902])) {
				$sql	.= $resource[902]." = ".$resource[902]." + :".$resource[902].", ";
				$params[':'.$resource[902]] = $costResources[902];
			}
			if(isset($costResources[903])) {
				$sql	.= $resource[903]." = ".$resource[903]." + :".$resource[903].", ";
				$params[':'.$resource[903]] = $costResources[903];
			}

			$sql = substr($sql, 0, -2);
			$sql .= " WHERE id = :techPlanet;";

			$db->update($sql, $params);
		}
		
		if(isset($costResources[921])) { $USER[$resource[921]]		+= $costResources[921]; }
		
		$USER['b_tech_id']			= 0;
		$USER['b_tech']      		= 0;
		$USER['b_tech_planet']		= 0;

		array_shift($CurrentQueue);

		if (count($CurrentQueue) == 0) {
			$USER['b_tech_queue']	= '';
			$USER['b_tech_planet']	= 0;
			$USER['b_tech_id']		= 0;
			$USER['b_tech']			= 0;
		} else {
			$BuildEndTime		= TIMESTAMP;
			$NewCurrentQueue	= array();
			foreach($CurrentQueue as $ListIDArray)
			{
				if($elementId == $ListIDArray[0] || empty($ListIDArray[0]))
					continue;
					
				if($ListIDArray[4] != $PLANET['id']) {
					$sql = "SELECT :resource6, :resource31, id FROM %%PLANETS%% WHERE id = :id;";
					$CPLANET = $db->selectSingle($sql, array(
						':resource6'	=> $resource[6],
						':resource31'	=> $resource[31],
						':id'			=> $ListIDArray[4]
					));
				} else
					$CPLANET		= $PLANET;
				
				$CPLANET[$resource[31].'_inter']	= $this->ecoObj->getNetworkLevel($USER, $CPLANET);
				$BuildEndTime       				+= BuildFunctions::getBuildingTime($USER, $CPLANET, $ListIDArray[0], NULL, false, $ListIDArray[1]);
				$ListIDArray[3]						= $BuildEndTime;
				$NewCurrentQueue[]					= $ListIDArray;				
			}
			
			if(!empty($NewCurrentQueue)) {
				$USER['b_tech']    			= TIMESTAMP;
				$USER['b_tech_queue'] 		= serialize($NewCurrentQueue);
				$this->ecoObj->setData($USER, $PLANET);
				$this->ecoObj->SetNextQueueTechOnTop();
				list($USER, $PLANET)		= $this->ecoObj->getData();
			} else {
				$USER['b_tech']    			= 0;
				$USER['b_tech_queue'] 		= '';
			}
		}

		return true;
	}

	private function RemoveBuildingFromQueue($QueueID)
	{
		global $USER, $PLANET, $resource;
		
		$CurrentQueue  = unserialize($USER['b_tech_queue']);
		if ($QueueID <= 1 || empty($CurrentQueue))
		{
			return false;
		}

		$ActualCount   = count($CurrentQueue);
		if ($ActualCount <= 1)
		{
			return $this->CancelBuildingFromQueue();
		}

		if(!isset($CurrentQueue[$QueueID - 2]))
		{
			return false;
		}
			
		$elementId 		= $CurrentQueue[$QueueID - 2][0];
		$BuildEndTime	= $CurrentQueue[$QueueID - 2][3];
		unset($CurrentQueue[$QueueID - 1]);
		$NewCurrentQueue	= array();
		foreach($CurrentQueue as $ID => $ListIDArray)
		{				
			if ($ID < $QueueID - 1) {
				$NewCurrentQueue[]	= $ListIDArray;
			} else {
				if($elementId == $ListIDArray[0])
					continue;

				if($ListIDArray[4] != $PLANET['id']) {
					$db = Database::get();

					$sql = "SELECT :resource6, :resource31 FROM %%PLANETS%% WHERE id = :id;";
					$CPLANET = $db->selectSingle($sql, array(
						':resource6'	=> $resource[6],
						':resource31'	=> $resource[31],
						':id'			=> $ListIDArray[4]
					));
				} else
					$CPLANET				= $PLANET;
				
				$CPLANET[$resource[31].'_inter']	= $this->ecoObj->getNetworkLevel($USER, $CPLANET);
				
				$BuildEndTime       += BuildFunctions::getBuildingTime($USER, $CPLANET, $ListIDArray[0], NULL, false, $ListIDArray[1]);
				$ListIDArray[3]		= $BuildEndTime;
				$NewCurrentQueue[]	= $ListIDArray;				
			}
		}
		
		if(!empty($NewCurrentQueue))
			$USER['b_tech_queue'] = serialize($NewCurrentQueue);
		else
			$USER['b_tech_queue'] = "";

		return true;
	}

	private function AddBuildingToQueue($elementId, $AddMode = true)
	{
		global $PLANET, $USER, $resource, $reslist, $pricelist;

		if(!in_array($elementId, $reslist['tech'])
			|| !BuildFunctions::isTechnologieAccessible($USER, $PLANET, $elementId)
		)
		{
			return 'unavailable';
		}

		if(!$this->CheckLabSettingsInQueue())
		{
			return 'busy';
		}

		$CurrentQueue = unserialize($USER['b_tech_queue'] ?? '');
		
		if (!empty($CurrentQueue)) {
			$ActualCount   	= count($CurrentQueue);
		} else {
			$CurrentQueue  	= array();
			$ActualCount   	= 0;
		}
				
		$maxTechQueue = Config::get()->max_elements_tech;
		if ($maxTechQueue != 0) {
			$maxTechQueue += Progression::premiumQueueSlots($USER);
		}
		if($maxTechQueue != 0 && $maxTechQueue <= $ActualCount)
		{
			return 'queuefull';
		}

		$BuildLevel					= $USER[$resource[$elementId]] + 1;
		if($ActualCount == 0)
		{
			if($pricelist[$elementId]['max'] < $BuildLevel)
			{
				return 'maxlevel';
			}

			$costResources		= BuildFunctions::getElementPrice($USER, $PLANET, $elementId, !$AddMode, $BuildLevel);
			
			if(!BuildFunctions::isElementBuyable($USER, $PLANET, $elementId, $costResources))
			{
				return 'resources';
			}
			
			if(isset($costResources[901])) { $PLANET[$resource[901]]	-= $costResources[901]; }
			if(isset($costResources[902])) { $PLANET[$resource[902]]	-= $costResources[902]; }
			if(isset($costResources[903])) { $PLANET[$resource[903]]	-= $costResources[903]; }
			if(isset($costResources[921])) { $USER[$resource[921]]		-= $costResources[921]; }
			
			$elementTime    			= BuildFunctions::getBuildingTime($USER, $PLANET, $elementId, $costResources);
			$BuildEndTime				= TIMESTAMP + $elementTime;
			
			$USER['b_tech_queue']		= serialize(array(array($elementId, $BuildLevel, $elementTime, $BuildEndTime, $PLANET['id'])));
			$USER['b_tech']				= $BuildEndTime;
			$USER['b_tech_id']			= $elementId;
			$USER['b_tech_planet']		= $PLANET['id'];
		} else {
			$addLevel = 0;
			foreach($CurrentQueue as $QueueSubArray)
			{
				if($QueueSubArray[0] != $elementId)
					continue;
					
				$addLevel++;
			}
				
			$BuildLevel					+= $addLevel;
				
			if($pricelist[$elementId]['max'] < $BuildLevel)
			{
				return 'maxlevel';
			}
				
			$elementTime    			= BuildFunctions::getBuildingTime($USER, $PLANET, $elementId, NULL, !$AddMode, $BuildLevel);
			
			$BuildEndTime				= $CurrentQueue[$ActualCount - 1][3] + $elementTime;
			$CurrentQueue[]				= array($elementId, $BuildLevel, $elementTime, $BuildEndTime, $PLANET['id']);
			$USER['b_tech_queue']		= serialize($CurrentQueue);
		}
		return true;
	}

	private function AddBuildingToQueueTarget($elementId, $TargetLevel, $AddMode = true)
	{
		global $USER, $resource, $pricelist;

		$TargetLevel	= (int) $TargetLevel;

		if($TargetLevel <= 0 || !isset($pricelist[$elementId]))
		{
			return false;
		}

		$ProjectedLevel	= (int) $USER[$resource[$elementId]];
		$CurrentQueue	= unserialize($USER['b_tech_queue'] ?? '');

		if(is_array($CurrentQueue))
		{
			foreach($CurrentQueue as $QueueEntry)
			{
				if(!is_array($QueueEntry) || (int) ($QueueEntry[0] ?? 0) !== (int) $elementId)
					continue;

				$ProjectedLevel++;
			}
		}

		if($pricelist[$elementId]['max'] > 0)
		{
			$TargetLevel	= min($TargetLevel, (int) $pricelist[$elementId]['max']);
		}

		$StartLevel	= $ProjectedLevel;
		$Queued		= 0;
		$Reason		= NULL;

		while($ProjectedLevel < $TargetLevel)
		{
			$Result	= $this->AddBuildingToQueue($elementId, $AddMode);

			if($Result !== true)
			{
				$Reason	= $Result;
				break;
			}

			$ProjectedLevel++;
			$Queued++;
		}

		if($Queued === 0 && $Reason === NULL)
		{
			$Reason	= $StartLevel >= $TargetLevel ? 'already' : 'unavailable';
		}

		$_SESSION['queueNotice']	= array(
			'type'		=> 'research',
			'element'	=> (int) $elementId,
			'from'		=> $StartLevel,
			'to'		=> $ProjectedLevel,
			'target'	=> $TargetLevel,
			'queued'	=> $Queued,
			'partial'	=> $Queued > 0 && $ProjectedLevel < $TargetLevel,
			'reason'	=> $Reason,
		);

		return $Queued > 0;
	}

	private function getQueueData()
	{
		global $LNG, $PLANET, $USER;

		$scriptData		= array();
		$quickinfo		= array();
		
		if ($USER['b_tech'] == 0)
		return array('queue' => $scriptData, 'quickinfo' => $quickinfo);
		
		$CurrentQueue   = unserialize($USER['b_tech_queue']);
		
		foreach($CurrentQueue as $BuildArray) {
			if ($BuildArray[3] < TIMESTAMP)
				continue;
			
			$PlanetName	= '';
		
			$quickinfo[$BuildArray[0]]	= $BuildArray[1];
			
			if($BuildArray[4] != $PLANET['id'])
				$PlanetName		= $USER['PLANETS'][$BuildArray[4]]['name'];
				
			$scriptData[] = array(
				'element'	=> $BuildArray[0], 
				'level' 	=> $BuildArray[1], 
				'time' 		=> $BuildArray[2], 
				'resttime' 	=> ($BuildArray[3] - TIMESTAMP), 
				'destroy' 	=> ($BuildArray[4] == 'destroy'), 
				'endtime' 	=> _date('U', $BuildArray[3], $USER['timezone']),
				'display' 	=> _date($LNG['php_tdformat'], $BuildArray[3], $USER['timezone']),
				'planet'	=> $PlanetName,
			);
		}
		
		return array('queue' => $scriptData, 'quickinfo' => $quickinfo);
	}

	public function show()
	{
		global $PLANET, $USER, $LNG, $resource, $reslist, $pricelist;
		
		if ($PLANET[$resource[31]] == 0)
		{
			$this->printMessage($LNG['bd_lab_required']);
		}
			
		$TheCommand		= HTTP::_GP('cmd','');
		$elementId     	= HTTP::_GP('tech', 0);
		$ListID     	= HTTP::_GP('listid', 0);
		$TargetLevel	= HTTP::_GP('target', 0);
		
		$PLANET[$resource[31].'_inter']	= ResourceUpdate::getNetworkLevel($USER, $PLANET);	

		if(!empty($TheCommand) && $_SERVER['REQUEST_METHOD'] === 'POST' && $USER['urlaubs_modus'] == 0)
		{
			switch($TheCommand)
			{
				case 'cancel':
					$this->CancelBuildingFromQueue();
				break;
				case 'remove':
					$this->RemoveBuildingFromQueue($ListID);
				break;
				case 'insert':
					if($TargetLevel > 0)
					{
						$this->AddBuildingToQueueTarget($elementId, $TargetLevel, true);
					}
					else
					{
						$this->AddBuildingToQueue($elementId, true);
					}
				break;
				case 'destroy':
					$this->AddBuildingToQueue($elementId, false);
				break;
			}
			
			$this->redirectTo('game.php?page=research');
		}
		
		$bContinue		= $this->CheckLabSettingsInQueue();
		
		$QueueNotice	= $this->getQueueNotice();
		$queueData		= $this->getQueueData();
		$TechQueue		= $queueData['queue'];
		$QueueCount		= count($TechQueue);
		$ResearchList	= array();

		foreach($reslist['tech'] as $elementId)
		{
			if (!BuildFunctions::isTechnologieAccessible($USER, $PLANET, $elementId))
				continue;
				
			if(isset($queueData['quickinfo'][$elementId]))
			{
				$levelToBuild	= $queueData['quickinfo'][$elementId];
			}
			else
			{
				$levelToBuild	= $USER[$resource[$elementId]];
			}
			
			$costResources		= BuildFunctions::getElementPrice($USER, $PLANET, $elementId, false, $levelToBuild+1);
			$costOverflow		= BuildFunctions::getRestPrice($USER, $PLANET, $elementId, $costResources);
			$elementTime    	= BuildFunctions::getBuildingTime($USER, $PLANET, $elementId, $costResources);
			$buyable			= $QueueCount != 0 || BuildFunctions::isElementBuyable($USER, $PLANET, $elementId, $costResources);

			$ResearchList[$elementId]	= array(
				'id'				=> $elementId,
				'level'				=> $USER[$resource[$elementId]],
				'maxLevel'			=> $pricelist[$elementId]['max'],
				'costResources'		=> $costResources,
				'costOverflow'		=> $costOverflow,
				'elementTime'    	=> $elementTime,
				'buyable'			=> $buyable,
				'levelToBuild'		=> $levelToBuild,
			);
		}
		
		$this->assign(array(
			'ResearchList'	=> $ResearchList,
			'IsLabinBuild'	=> !$bContinue,
			'IsFullQueue'	=> Config::get()->max_elements_tech == 0 || (Config::get()->max_elements_tech + Progression::premiumQueueSlots($USER)) <= count($TechQueue),
			'Queue'			=> $TechQueue,
			'QueueNotice'	=> $QueueNotice,
		));
		
		$this->display('page.research.default.tpl');
	}
}
