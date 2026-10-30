<?php

/**
 * Planetarium (live page=planet): buy fields with Dark Matter and teleport a
 * planet. Prices follow the captured live curves in class.BlackMarket.
 */
class ShowPlanetPage extends AbstractGamePage
{
	public static $requireModule = MODULE_TRADER;

	function __construct()
	{
		parent::__construct();
	}

	public function show()
	{
		global $USER, $PLANET, $resource, $LNG;

		$db = Database::get();
		$config = Config::get();

		$rows = $db->select(
			"SELECT * FROM %%PLANETS%% WHERE id_owner = :userId AND destruyed = 0 ORDER BY id ASC;",
			array(':userId' => $USER['id'])
		);

		$eco = new ResourceUpdate();
		$celestials = array();

		foreach ($rows as $row) {
			list(, $row) = $eco->CalcResource($USER, $row, false);

			$celestials[] = array(
				'id'			=> (int)$row['id'],
				'name'			=> $row['name'],
				'type'			=> (int)$row['planet_type'],
				'galaxy'		=> (int)$row['galaxy'],
				'system'		=> (int)$row['system'],
				'planet'		=> (int)$row['planet'],
				'diameter'		=> (int)$row['diameter'],
				'fieldsCurrent'	=> (int)$row['field_current'],
				'fieldsMax'		=> CalculateMaxPlanetFields($row),
				'fieldsBought'	=> (int)$row['fields_bought'],
				'fieldPrice'	=> BlackMarket::fieldExpansionCost((int)$row['fields_bought'], 1),
				'isMoon'		=> (int)$row['planet_type'] === 3,
				'canMove'		=> (int)$row['planet_type'] === 1,
			);
		}

		$errorKey = HTTP::_GP('error', '');
		$error = $errorKey !== '' && isset($LNG['bm_error'][$errorKey]) ? $LNG['bm_error'][$errorKey] : '';

		$this->assign(array(
			'celestials'	=> $celestials,
			'darkmatter'	=> (float)$USER['darkmatter'],
			'stardust'		=> (float)$USER['stardust'],
			'done'			=> HTTP::_GP('done', ''),
			'error'			=> $error,
			'maxGalaxy'		=> (int)$config->max_galaxy,
			'maxSystem'		=> (int)$config->max_system,
			'maxPlanets'	=> (int)$config->max_planets,
		));

		$this->display('page.planet.default.tpl');
	}

	public function fields()
	{
		global $USER, $PLANET, $LNG;

		$planetId = HTTP::_GP('planet', 0);
		$amount = max(1, min(100, HTTP::_GP('fields', 1)));

		$db = Database::get();
		$row = $db->selectSingle(
			"SELECT id, field_max, fields_bought FROM %%PLANETS%% WHERE id = :planetId AND id_owner = :userId AND destruyed = 0;",
			array(':planetId' => $planetId, ':userId' => $USER['id'])
		);

		if (empty($row)) {
			$this->redirectTo('game.php?page=planet&error=unavailable');
		}

		$purchased = (int)$row['fields_bought'];
		$price = BlackMarket::fieldExpansionCost($purchased, $amount);
		if ($price <= 0 || (float)$USER['darkmatter'] < $price) {
			$this->redirectTo('game.php?page=planet&error=darkmatter');
		}

		$db->update(
			"UPDATE %%USERS%% SET darkmatter = darkmatter - :price WHERE id = :userId AND darkmatter >= :price;",
			array(':price' => $price, ':userId' => $USER['id'])
		);
		$db->update(
			"UPDATE %%PLANETS%% SET field_max = field_max + :amount, fields_bought = fields_bought + :amount WHERE id = :planetId;",
			array(':amount' => $amount, ':planetId' => $planetId)
		);

		$USER['darkmatter'] = (float)$USER['darkmatter'] - $price;
		if ((int)$PLANET['id'] === (int)$planetId) {
			$PLANET['field_max'] = (int)$PLANET['field_max'] + $amount;
			$PLANET['fields_bought'] = (int)$PLANET['fields_bought'] + $amount;
		}

		$this->redirectTo('game.php?page=planet&done=fields');
	}

	public function diameter()
	{
		global $USER, $PLANET, $LNG;

		$planetId = HTTP::_GP('planet', 0);
		$db = Database::get();

		$row = $db->selectSingle(
			"SELECT * FROM %%PLANETS%% WHERE id = :planetId AND id_owner = :userId AND destruyed = 0;",
			array(':planetId' => $planetId, ':userId' => $USER['id'])
		);
		if (empty($row) || !in_array((int)$row['planet_type'], array(1, 3), true)) {
			$this->redirectTo('game.php?page=planet&error=unavailable');
		}

		$debris = self::debrisRow($db, $row);
		if (empty($debris)
			|| (float)$debris['der_metal'] < BlackMarket::DIAMETER_METAL_DEBRIS
			|| (float)$debris['der_crystal'] < BlackMarket::DIAMETER_CRYSTAL_DEBRIS) {
			$this->redirectTo('game.php?page=planet&error=debris');
		}
		if ((float)$USER['stardust'] < BlackMarket::DIAMETER_STARDUST) {
			$this->redirectTo('game.php?page=planet&error=stardust');
		}

		list($diameterGain, $fieldGain) = BlackMarket::diameterGains(Expedition::rand(), Expedition::rand());
		$handle = $db->getHandle();

		try {
			$handle->beginTransaction();

			$db->update(
				"UPDATE %%USERS%% SET stardust = stardust - :cost WHERE id = :userId AND stardust >= :cost;",
				array(':cost' => BlackMarket::DIAMETER_STARDUST, ':userId' => $USER['id'])
			);
			$db->update(
				"UPDATE %%PLANETS%% SET der_metal = der_metal - :metal, der_crystal = der_crystal - :crystal
					WHERE id = :debrisId AND der_metal >= :metal AND der_crystal >= :crystal;",
				array(
					':metal' => BlackMarket::DIAMETER_METAL_DEBRIS,
					':crystal' => BlackMarket::DIAMETER_CRYSTAL_DEBRIS,
					':debrisId' => $debris['id'],
				)
			);
			$db->update(
				"UPDATE %%PLANETS%% SET diameter = diameter + :diameter, field_max = field_max + :fields WHERE id = :planetId;",
				array(':diameter' => $diameterGain, ':fields' => $fieldGain, ':planetId' => $planetId)
			);

			$handle->commit();
		} catch (Exception $e) {
			if ($handle->inTransaction()) {
				$handle->rollBack();
			}
			throw $e;
		}

		$USER['stardust'] = (float)$USER['stardust'] - BlackMarket::DIAMETER_STARDUST;
		if ((int)$PLANET['id'] === (int)$planetId) {
			$PLANET['diameter'] = (int)$PLANET['diameter'] + $diameterGain;
			$PLANET['field_max'] = (int)$PLANET['field_max'] + $fieldGain;
		}

		$this->redirectTo('game.php?page=planet&done=diameter');
	}

	private static function debrisRow($db, $row)
	{
		if ((int)$row['planet_type'] === 1) {
			return $row;
		}
		return $db->selectSingle(
			"SELECT id, der_metal, der_crystal FROM %%PLANETS%% WHERE id_luna = :moonId;",
			array(':moonId' => $row['id'])
		);
	}

	public function relocate()
	{
		global $USER, $PLANET, $LNG;

		$planetId = HTTP::_GP('planet', 0);
		$galaxy = HTTP::_GP('galaxy', 0);
		$system = HTTP::_GP('system', 0);
		$position = HTTP::_GP('position', 0);
		$config = Config::get();

		$db = Database::get();
		$row = $db->selectSingle(
			"SELECT * FROM %%PLANETS%% WHERE id = :planetId AND id_owner = :userId AND destruyed = 0 AND planet_type = '1';",
			array(':planetId' => $planetId, ':userId' => $USER['id'])
		);

		if (empty($row)) {
			$this->redirectTo('game.php?page=planet&error=unavailable');
		}

		if (!BlackMarket::validRelocationTarget($galaxy, $system, $position, $config->max_galaxy, $config->max_system, $config->max_planets)
			|| BlackMarket::sameCoordinates($row['galaxy'], $row['system'], $row['planet'], $galaxy, $system, $position)) {
			$this->redirectTo('game.php?page=planet&error=target');
		}

		if (BlackMarket::relocationLeavesSystem($row['galaxy'], $row['system'], $galaxy, $system)
			&& (int)$row['last_relocation'] > TIMESTAMP - BlackMarket::RELOCATION_COOLDOWN) {
			$this->redirectTo('game.php?page=planet&error=cooldown');
		}

		$occupied = $db->selectSingle(
			"SELECT id FROM %%PLANETS%% WHERE galaxy = :galaxy AND system = :system AND planet = :position AND planet_type = '1' AND destruyed = 0;",
			array(':galaxy' => $galaxy, ':system' => $system, ':position' => $position)
		);
		if (!empty($occupied)) {
			$this->redirectTo('game.php?page=planet&error=occupied');
		}

		$price = BlackMarket::relocationCost($row['galaxy'], $row['system'], $row['planet'], $galaxy, $system, $position);
		if ($price > 0 && (float)$USER['darkmatter'] < $price) {
			$this->redirectTo('game.php?page=planet&error=darkmatter');
		}

		$handle = $db->getHandle();
		try {
			$handle->beginTransaction();

			if ($price > 0) {
				$db->update(
					"UPDATE %%USERS%% SET darkmatter = darkmatter - :price WHERE id = :userId AND darkmatter >= :price;",
					array(':price' => $price, ':userId' => $USER['id'])
				);
			}
			$db->update(
				"UPDATE %%PLANETS%% SET galaxy = :galaxy, system = :system, planet = :position,
					last_relocation = :now, attack_locked_until = :locked WHERE id = :planetId;",
				array(
					':galaxy'	=> $galaxy,
					':system'	=> $system,
					':position'	=> $position,
					':now'		=> TIMESTAMP,
					':locked'	=> TIMESTAMP + BlackMarket::ATTACK_LOCK_TIME,
					':planetId'	=> $planetId,
				)
			);
			$db->update(
				"UPDATE %%PLANETS%% SET galaxy = :galaxy, system = :system, planet = :position,
					last_relocation = :now WHERE id_luna = :planetId AND planet_type = '3';",
				array(
					':galaxy'	=> $galaxy,
					':system'	=> $system,
					':position'	=> $position,
					':now'		=> TIMESTAMP,
					':planetId'	=> $planetId,
				)
			);
			$handle->commit();
		} catch (Exception $e) {
			if ($handle->inTransaction()) {
				$handle->rollBack();
			}
			$this->redirectTo('game.php?page=planet&error=unavailable');
		}

		$USER['darkmatter'] = (float)$USER['darkmatter'] - $price;
		if ((int)$PLANET['id'] === (int)$planetId) {
			$PLANET['galaxy'] = (int)$galaxy;
			$PLANET['system'] = (int)$system;
			$PLANET['planet'] = (int)$position;
			$PLANET['last_relocation'] = TIMESTAMP;
			$PLANET['attack_locked_until'] = TIMESTAMP + BlackMarket::ATTACK_LOCK_TIME;
		}

		$this->redirectTo('game.php?page=planet&done=relocate');
	}
}
