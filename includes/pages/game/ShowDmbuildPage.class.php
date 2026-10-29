<?php

/**
 * Buildings Market (live page=dmbuild): buy one instant building level with
 * Dark Matter. Uses the same prerequisites/costs as the normal queue but does
 * not touch it.
 */
class ShowDmbuildPage extends AbstractGamePage
{
	public static $requireModule = MODULE_BUILDING;

	function __construct()
	{
		parent::__construct();
	}

	private function maxLevel($element, $one)
	{
		global $pricelist;

		if (in_array($element, $one)) {
			return 1;
		}
		if (isset($pricelist[$element]['max']) && $pricelist[$element]['max'] > 0) {
			return (int)$pricelist[$element]['max'];
		}
		return 255;
	}

	public function show()
	{
		global $USER, $PLANET, $resource, $reslist, $LNG;

		$maxFields = CalculateMaxPlanetFields($PLANET);
		$list = array();

		foreach ($reslist['allow'][$PLANET['planet_type']] as $element) {
			if (!in_array($element, $reslist['build']) || !BuildFunctions::isTechnologieAccessible($USER, $PLANET, $element)) {
				continue;
			}

			$level = (int)$PLANET[$resource[$element]];
			$max = $this->maxLevel($element, $reslist['one']);
			if ($level >= $max) {
				continue;
			}

			$next = $level + 1;
			$cost = BuildFunctions::getElementPrice($USER, $PLANET, $element, false, $next);

			$list[$element] = array(
				'name'		=> $LNG['tech'][$element] ?? $element,
				'level'		=> $level,
				'next'		=> $next,
				'cost'		=> $cost,
				'darkmatter'=> BlackMarket::dmMarketPrice($cost),
				'noFields'	=> $PLANET['field_current'] >= $maxFields,
			);
		}

		$errorKey = HTTP::_GP('error', '');
		$error = $errorKey !== '' && isset($LNG['bm_error'][$errorKey]) ? $LNG['bm_error'][$errorKey] : '';

		$this->assign(array(
			'list'			=> $list,
			'done'			=> HTTP::_GP('done', 0) == 1,
			'error'			=> $error,
			'darkmatter'	=> (float)$USER['darkmatter'],
			'fieldFree'		=> max(0, $maxFields - $PLANET['field_current']),
		));

		$this->display('page.dmbuild.default.tpl');
	}

	public function buy()
	{
		global $USER, $PLANET, $resource, $reslist, $pricelist, $LNG;

		$element = HTTP::_GP('element', 0);
		if (!in_array($element, $reslist['allow'][$PLANET['planet_type']])
			|| !in_array($element, $reslist['build'])
			|| !BuildFunctions::isTechnologieAccessible($USER, $PLANET, $element)) {
			$this->redirectTo('game.php?page=dmbuild&error=unavailable');
		}

		$level = (int)$PLANET[$resource[$element]];
		$max = $this->maxLevel($element, $reslist['one']);
		if ($level >= $max) {
			$this->redirectTo('game.php?page=dmbuild&error=maxlevel');
		}
		if ($PLANET['field_current'] >= CalculateMaxPlanetFields($PLANET)) {
			$this->redirectTo('game.php?page=dmbuild&error=nofields');
		}

		$cost = BuildFunctions::getElementPrice($USER, $PLANET, $element, false, $level + 1);
		$price = BlackMarket::dmMarketPrice($cost);
		if ($price <= 0 || (float)$USER['darkmatter'] < $price) {
			$this->redirectTo('game.php?page=dmbuild&error=darkmatter');
		}

		$db = Database::get();
		$column = $resource[$element];

		$db->update(
			"UPDATE %%USERS%% SET darkmatter = darkmatter - :price WHERE id = :userId AND darkmatter >= :price;",
			array(':price' => $price, ':userId' => $USER['id'])
		);
		$db->update(
			"UPDATE %%PLANETS%% SET `".$column."` = `".$column."` + 1, field_current = field_current + 1 WHERE id = :planetId;",
			array(':planetId' => $PLANET['id'])
		);

		$USER['darkmatter'] = (float)$USER['darkmatter'] - $price;
		$PLANET[$column] = $level + 1;
		$PLANET['field_current'] = (int)$PLANET['field_current'] + 1;

		$this->redirectTo('game.php?page=dmbuild&done=1');
	}
}
