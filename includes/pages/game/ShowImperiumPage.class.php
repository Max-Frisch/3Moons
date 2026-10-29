<?php

/**
 * Empire overview (live page=imperium): one matrix of every owned celestial in
 * creation order with resources, hourly production (including Collider Dark
 * Matter), fields, buildings, ships and defenses.
 */
class ShowImperiumPage extends AbstractGamePage
{
	public static $requireModule = MODULE_IMPERIUM;

	function __construct()
	{
		parent::__construct();
	}

	function show()
	{
		global $USER, $PLANET, $resource, $reslist;

		$db = Database::get();
		$config = Config::get();

		$sql = "SELECT * FROM %%PLANETS%% WHERE id_owner = :userID AND destruyed = '0' ORDER BY id ASC;";
		$PlanetsRAW = $db->select($sql, array(':userID' => $USER['id']));

		$PlanetRess = new ResourceUpdate();
		$PLANETS = array();

		foreach ($PlanetsRAW as $CPLANET) {
			list($USER, $CPLANET) = $PlanetRess->CalcResource($USER, $CPLANET, true);
			$PLANETS[] = $CPLANET;
		}

		$planetList = array(
			'name'			=> array(),
			'image'			=> array(),
			'coords'		=> array(),
			'field'			=> array(),
			'resource'		=> array(),
			'production'	=> array(),
			'build'			=> array(),
			'fleet'			=> array(),
			'defense'		=> array(),
		);

		$basicIncome = array(
			901	=> (float)$config->metal_basic_income * (float)$config->resource_multiplier,
			902	=> (float)$config->crystal_basic_income * (float)$config->resource_multiplier,
			903	=> (float)$config->deuterium_basic_income * (float)$config->resource_multiplier,
		);

		foreach ($PLANETS as $Planet) {
			$planetId = (int)$Planet['id'];

			$planetList['name'][$planetId]				= $Planet['name'];
			$planetList['image'][$planetId]				= $Planet['image'];
			$planetList['coords'][$planetId]			= array(
				'galaxy'	=> $Planet['galaxy'],
				'system'	=> $Planet['system'],
				'planet'	=> $Planet['planet'],
				'type'		=> (int)$Planet['planet_type'],
			);
			$planetList['field'][$planetId]				= array(
				'current'	=> $Planet['field_current'],
				'max'		=> CalculateMaxPlanetFields($Planet),
			);

			$planetList['resource'][901][$planetId]		= $Planet['metal'];
			$planetList['resource'][902][$planetId]		= $Planet['crystal'];
			$planetList['resource'][903][$planetId]		= $Planet['deuterium'];
			$planetList['resource'][911][$planetId]		= $Planet['energy'];

			$isPlanet = (int)$Planet['planet_type'] === 1 && $USER['urlaubs_modus'] == 0;
			foreach (array(901, 902, 903) as $resourceId) {
				$planetList['production'][$resourceId][$planetId] =
					(float)$Planet[$resource[$resourceId].'_perhour'] + ($isPlanet ? $basicIncome[$resourceId] : 0);
			}
			$collider = (int)$Planet['planet_type'] === 3 ? (int)$Planet['kollaider'] : 0;
			$planetList['production'][921][$planetId] = $collider > 0
				? Collider::productionForUser($collider, $USER)
				: 0;

			foreach ($reslist['build'] as $elementID) {
				$planetList['build'][$elementID][$planetId] = isset($Planet[$resource[$elementID]]) ? $Planet[$resource[$elementID]] : 0;
			}
			foreach ($reslist['fleet'] as $elementID) {
				$planetList['fleet'][$elementID][$planetId] = isset($Planet[$resource[$elementID]]) ? $Planet[$resource[$elementID]] : 0;
			}
			foreach ($reslist['defense'] as $elementID) {
				$planetList['defense'][$elementID][$planetId] = isset($Planet[$resource[$elementID]]) ? $Planet[$resource[$elementID]] : 0;
			}
		}

		$this->assign(array(
			'colspan'		=> count($PLANETS) + 2,
			'planetList'	=> $planetList,
		));

		$this->display('page.empire.default.tpl');
	}
}
