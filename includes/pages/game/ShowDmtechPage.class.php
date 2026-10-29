<?php

/**
 * Technology Market (live page=dmtech): buy one instant research level with
 * Dark Matter. Prerequisites, lab availability and max level are enforced.
 */
class ShowDmtechPage extends AbstractGamePage
{
	public static $requireModule = MODULE_RESEARCH;

	function __construct()
	{
		parent::__construct();
	}

	public function show()
	{
		global $USER, $PLANET, $resource, $reslist, $pricelist, $LNG;

		$list = array();

		foreach ($reslist['tech'] as $element) {
			if (!BuildFunctions::isTechnologieAccessible($USER, $PLANET, $element)) {
				continue;
			}

			$level = (int)$USER[$resource[$element]];
			$max = isset($pricelist[$element]['max']) && $pricelist[$element]['max'] > 0
				? (int)$pricelist[$element]['max']
				: 255;
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
			);
		}

		$errorKey = HTTP::_GP('error', '');
		$error = $errorKey !== '' && isset($LNG['bm_error'][$errorKey]) ? $LNG['bm_error'][$errorKey] : '';

		$this->assign(array(
			'list'			=> $list,
			'done'			=> HTTP::_GP('done', 0) == 1,
			'error'			=> $error,
			'darkmatter'	=> (float)$USER['darkmatter'],
		));

		$this->display('page.dmtech.default.tpl');
	}

	public function buy()
	{
		global $USER, $PLANET, $resource, $reslist, $pricelist, $LNG;

		$element = HTTP::_GP('element', 0);
		if (!in_array($element, $reslist['tech'])
			|| !BuildFunctions::isTechnologieAccessible($USER, $PLANET, $element)) {
			$this->redirectTo('game.php?page=dmtech&error=unavailable');
		}

		$level = (int)$USER[$resource[$element]];
		$max = isset($pricelist[$element]['max']) && $pricelist[$element]['max'] > 0
			? (int)$pricelist[$element]['max']
			: 255;
		if ($level >= $max) {
			$this->redirectTo('game.php?page=dmtech&error=maxlevel');
		}

		$cost = BuildFunctions::getElementPrice($USER, $PLANET, $element, false, $level + 1);
		$price = BlackMarket::dmMarketPrice($cost);
		if ($price <= 0 || (float)$USER['darkmatter'] < $price) {
			$this->redirectTo('game.php?page=dmtech&error=darkmatter');
		}

		$db = Database::get();
		$column = $resource[$element];

		$db->update(
			"UPDATE %%USERS%% SET `".$column."` = `".$column."` + 1, darkmatter = darkmatter - :price WHERE id = :userId AND darkmatter >= :price;",
			array(':price' => $price, ':userId' => $USER['id'])
		);

		$USER[$column] = $level + 1;
		$USER['darkmatter'] = (float)$USER['darkmatter'] - $price;

		$this->redirectTo('game.php?page=dmtech&done=1');
	}
}
