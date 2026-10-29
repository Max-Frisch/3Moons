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


class ShowOfficierPage extends AbstractGamePage
{
	public static $requireModule = 0;

	function __construct() 
	{
		parent::__construct();
	}

	private function UpdateOfficier($Element)
	{
		global $USER, $PLANET, $resource, $pricelist;
		
		$costResources		= BuildFunctions::getElementPrice($USER, $PLANET, $Element);
			
		if (!BuildFunctions::isTechnologieAccessible($USER, $PLANET, $Element) 
			|| !BuildFunctions::isElementBuyable($USER, $PLANET, $Element, $costResources) 
			|| $pricelist[$Element]['max'] <= $USER[$resource[$Element]]) {
			return;
		}
		
		$USER[$resource[$Element]]	+= 1;
		
		if(isset($costResources[901])) { $PLANET[$resource[901]]	-= $costResources[901]; }
		if(isset($costResources[902])) { $PLANET[$resource[902]]	-= $costResources[902]; }
		if(isset($costResources[903])) { $PLANET[$resource[903]]	-= $costResources[903]; }
		if(isset($costResources[921])) { $USER[$resource[921]]		-= $costResources[921]; }

		$sql	= 'UPDATE %%USERS%% SET
		'.$resource[$Element].' = :newTime
		WHERE
		id = :userId;';

		Database::get()->update($sql, array(
			':newTime'	=> $USER[$resource[$Element]],
			':userId'	=> $USER['id']
		));
	}

	private function UpdateGovernator($action, $Element)
	{
		global $USER, $resource;

		$governators	= Achievements::getGovernators();
		if(!isset($governators[$Element])) {
			return;
		}

		$level	= Achievements::getGovernatorLevel($USER, $Element);
		$max	= $governators[$Element]['max'];

		if($action === 'improve') {
			if($level >= $max) {
				return;
			}

			$cost	= Achievements::governatorAPCost($Element, $level);
			if($USER['achievement_points'] < $cost) {
				return;
			}

			$column	= 'gov_level_'.$Element;
			Database::get()->update('UPDATE %%USERS%% SET
				`'.$column.'` = `'.$column.'` + 1,
				achievement_points = achievement_points - :cost,
				achievement_points_spent = achievement_points_spent + :cost
				WHERE id = :userId;', array(
				':cost'		=> $cost,
				':userId'	=> $USER['id'],
			));

			$USER[$column]					= $level + 1;
			$USER['achievement_points']		-= $cost;
			$USER['achievement_points_spent'] += $cost;
			return;
		}

		if($action === 'hire') {
			$days		= max(1, min(7, (int)HTTP::_GP('days', 1)));
			$priceDay	= Achievements::governatorDMPrice($Element, $level);
			$cost		= $priceDay * $days;

			if($USER['darkmatter'] < $cost) {
				return;
			}

			$column		= $resource[$Element];
			$activeTill	= max((int)$USER[$column], TIMESTAMP);
			$newTime	= $activeTill + $days * 86400;

			Database::get()->update('UPDATE %%USERS%% SET
				`'.$column.'` = :newTime,
				darkmatter = darkmatter - :cost
				WHERE id = :userId;', array(
				':newTime'	=> $newTime,
				':cost'		=> $cost,
				':userId'	=> $USER['id'],
			));

			$USER[$column]		= $newTime;
			$USER['darkmatter']	-= $cost;
		}
	}
	
	public function show()
	{
		global $USER, $PLANET, $resource, $reslist, $LNG, $pricelist;
		
		$updateID	  = HTTP::_GP('id', 0);
		$action		  = HTTP::_GP('action', '');
		$governators  = Achievements::getGovernators();
				
		if (!empty($updateID) && $_SERVER['REQUEST_METHOD'] === 'POST' && $USER['urlaubs_modus'] == 0)
		{
			if(isModuleAvailable(MODULE_DMEXTRAS) && isset($governators[$updateID]))
			{
				$this->UpdateGovernator($action, $updateID);
			}
			elseif(isModuleAvailable(MODULE_OFFICIER) && in_array($updateID, $reslist['officier'])) {
				$this->UpdateOfficier($updateID);
			}
		}
		
		$governatorList	= array();
		$officierList	= array();
		
		if(isModuleAvailable(MODULE_DMEXTRAS))
		{
			foreach(Achievements::getGovernators() as $Element => $Governator)
			{
				$level		= Achievements::getGovernatorLevel($USER, $Element);
				$timeLeft	= max((int)$USER[$resource[$Element]] - TIMESTAMP, 0);
				$apCost		= Achievements::governatorAPCost($Element, $level);
				$dmPerDay	= Achievements::governatorDMPrice($Element, $level);
				$bonus		= array();

				foreach(Achievements::governatorBonus($Element, $level) as $BonusName => $BonusPct)
				{
					$bonus[] = array(
						'name'	=> $LNG['bonus'][$BonusName],
						'pct'	=> $BonusPct,
					);
				}

				$governatorList[$Element]	= array(
					'level'			=> $level,
					'maxLevel'		=> $Governator['max'],
					'apCost'		=> $apCost,
					'apBuyable'		=> $level < $Governator['max'] && $USER['achievement_points'] >= $apCost,
					'dmPerDay'		=> $dmPerDay,
					'dmBuyable'		=> $USER['darkmatter'] >= $dmPerDay,
					'timeLeft'		=> $timeLeft,
					'bonus'			=> $bonus,
				);
			}
		}
		
		if(isModuleAvailable(MODULE_OFFICIER))
		{
			foreach($reslist['officier'] as $Element)
			{
				if (!BuildFunctions::isTechnologieAccessible($USER, $PLANET, $Element))
					continue;
					
				$costResources		= BuildFunctions::getElementPrice($USER, $PLANET, $Element);
				$buyable			= BuildFunctions::isElementBuyable($USER, $PLANET, $Element, $costResources);
				$costOverflow		= BuildFunctions::getRestPrice($USER, $PLANET, $Element, $costResources);
				$elementBonus		= BuildFunctions::getAvalibleBonus($Element);
				
				$officierList[$Element]	= array(
					'level'				=> $USER[$resource[$Element]],
					'maxLevel'			=> $pricelist[$Element]['max'],
					'costResources'		=> $costResources,
					'buyable'			=> $buyable,
					'costOverflow'		=> $costOverflow,
					'elementBonus'		=> $elementBonus,
				);
			}
		}
		
		$this->assign(array(
			'governatorList'	=> $governatorList,
			'officierList'		=> $officierList,
			'achievementPoints'	=> pretty_number($USER['achievement_points']),
			'achievementPointsSpent' => pretty_number($USER['achievement_points_spent']),
			'of_dm_trade'		=> sprintf($LNG['of_dm_trade'], $LNG['tech'][921]),
		));
		
		$this->display('page.officier.default.tpl');
	}
}
