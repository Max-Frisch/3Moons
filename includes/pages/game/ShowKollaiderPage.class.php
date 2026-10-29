<?php

/**
 * Moon Collider (live page=kollaider): moon-only, bought with Antimatter, earns
 * Dark Matter per hour for the whole account. Production is accrued in
 * Collider::accrue() on each ingame request.
 */
class ShowKollaiderPage extends AbstractGamePage
{
	public static $requireModule = MODULE_BUILDING;

	function __construct()
	{
		parent::__construct();
	}

	public function show()
	{
		global $USER, $PLANET, $LNG;

		if ((int)$PLANET['planet_type'] !== 3) {
			$this->printMessage($LNG['bm_collider_moon_only'], array(array(
				'label'	=> $LNG['sys_back'],
				'url'	=> 'game.php?page=overview'
			)));
			return;
		}

		$level = (int)$PLANET['kollaider'];
		$next = $level + 1;
		$production = Collider::productionForUser($level, $USER);
		$nextProduction = Collider::productionForUser($next, $USER);

		$errorKey = HTTP::_GP('error', '');
		$error = $errorKey !== '' && isset($LNG['bm_error'][$errorKey]) ? $LNG['bm_error'][$errorKey] : '';

		$this->assign(array(
			'level'				=> $level,
			'maxLevel'			=> Collider::MAX_LEVEL,
			'production'		=> $production,
			'nextProduction'	=> $nextProduction,
			'cost'				=> Collider::cost($next),
			'antimatter'		=> (float)$USER['antimatter'],
			'done'				=> HTTP::_GP('done', 0) == 1,
			'error'				=> $error,
		));

		$this->display('page.kollaider.default.tpl');
	}

	public function build()
	{
		global $USER, $PLANET, $LNG;

		if ((int)$PLANET['planet_type'] !== 3) {
			$this->redirectTo('game.php?page=overview');
		}

		$level = (int)$PLANET['kollaider'];
		if ($level >= Collider::MAX_LEVEL) {
			$this->redirectTo('game.php?page=kollaider&error=maxlevel');
		}

		$cost = Collider::cost($level + 1);
		if ($cost <= 0 || (float)$USER['antimatter'] < $cost) {
			$this->redirectTo('game.php?page=kollaider&error=antimatter');
		}

		$db = Database::get();
		$db->update(
			"UPDATE %%USERS%% SET antimatter = antimatter - :cost WHERE id = :userId AND antimatter >= :cost;",
			array(':cost' => $cost, ':userId' => $USER['id'])
		);
		$db->update(
			"UPDATE %%PLANETS%% SET kollaider = kollaider + 1 WHERE id = :planetId;",
			array(':planetId' => $PLANET['id'])
		);

		$USER['antimatter'] = (float)$USER['antimatter'] - $cost;
		$PLANET['kollaider'] = $level + 1;

		$this->redirectTo('game.php?page=kollaider&done=1');
	}
}
