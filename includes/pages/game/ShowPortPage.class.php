<?php

/**
 * Black market hub (live page=port).
 */
class ShowPortPage extends AbstractGamePage
{
	public static $requireModule = 0;

	function __construct()
	{
		parent::__construct();
	}

	function show()
	{
		global $LNG;

		$this->assign(array(
			'tiles'	=> array(
				array('page=trader', 'bm_resource_trader', 'bm_resource_trader_desc'),
				array('page=fleetDealer', 'bm_merchant', 'bm_merchant_desc'),
				array('page=planet', 'bm_planetarium', 'bm_planetarium_desc'),
				array('page=dmbuild', 'bm_buildings_market', 'bm_buildings_market_desc'),
				array('page=dmtech', 'bm_technology_market', 'bm_technology_market_desc'),
				array('page=reward2', 'bm_voucher', 'bm_voucher_desc'),
			),
		));

		$this->display('page.port.default.tpl');
	}
}
