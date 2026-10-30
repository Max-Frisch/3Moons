<?php

/**
 * Focused checks for the P7 catalog/combat/expedition parity slice.
 * Run with: docker compose exec -T web php tests/p7_math.php
 */

require_once __DIR__ . '/../includes/libs/opbe/utils/includer.php';
require_once __DIR__ . '/../includes/classes/class.CombatStats.php';
require_once __DIR__ . '/../includes/classes/class.Arsenal.php';
require_once __DIR__ . '/../includes/classes/class.Academy.php';
require_once __DIR__ . '/../includes/classes/class.BlackMarket.php';
require_once __DIR__ . '/../includes/classes/class.Expedition.php';

$fail = 0;
$pass = 0;

function check($label, $actual, $expected)
{
    global $fail, $pass;
    if ($actual == $expected) {
        $pass++;
        return;
    }
    $fail++;
    echo "FAIL {$label}: got " . var_export($actual, true) . ', expected ' . var_export($expected, true) . "\n";
}

$curve = array(0, 1, 2, 4, 6, 9, 12, 16, 20, 25, 30, 36, 42, 49, 56, 64, 72, 81, 90, 100, 110);
for ($level = 0; $level <= 20; $level++) {
    check('tech curve L'.$level, CombatStats::techBonusPct($level), $curve[$level]);
}

check('live rounds', ROUNDS, 8);
check('live ship debris', SHIP_DEBRIS_FACTOR, 0.5);
check('live defense repair', DEFENSE_REPAIR_PROB, 0.61);

check('hull light fighter', CombatStats::hull(204), 400);
check('hull battleship', CombatStats::hull(207), 5800);
check('hull black moon', CombatStats::hull(216), 1200000);
check('hull ONill', CombatStats::hull(221), 52000000);
check('hull Flying Death', CombatStats::hull(222), 33000000);
check('hull Lepton', CombatStats::hull(412), 1500000);
check('hull Particle Emitter', CombatStats::hull(419), 7500000);
check('unknown hull', CombatStats::hull(999), NULL);

$ship = new ShipType(207, 1, array(), 190, array(41000, 17000), 700, 0, 0, 0, 5800);
check('ShipType captured hull', $ship->getHull(), 5800);
$ship->setWeaponsTech(10);
$ship->setShieldsTech(10);
$ship->setArmourTech(10);
check('quadratic attack at L10', $ship->getPower(), 910);
check('quadratic shield at L10', $ship->getShield(), 247);
check('quadratic hull at L10', $ship->getHull(), 7540);

$owned = array(
    1 => array('value' => 5.0),
    2 => array('value' => 10.0),
);
$player = array('laser_tech' => 10, 'ionic_tech' => 5, 'buster_tech' => 0, 'graviton_tech' => 0);
check('Galleon has two weapon components', count(Arsenal::weaponComponents(225)), 2);
check('Light Cargo has no weapon components', count(Arsenal::weaponComponents(202)), 0);
check('Galleon component attack', round(Arsenal::componentAttackPower($owned, 225, 20000, $player), 6), 24500.0);
check('Black Moon component attack', round(Arsenal::componentAttackPower(array(), 216, 135000, $player), 6), 139050.0);
check('no component map falls back', Arsenal::componentAttackPower($owned, 202, 0, $player), NULL);
check('graviton tech pct', Arsenal::weaponTechPct(array('graviton_tech' => 5), 'gravitational'), 20.0);
check('standard tech pct', Arsenal::weaponTechPct(array('laser_tech' => 99), 'standard'), 0.0);
check('light fighter conveyor class', Arsenal::conveyorClass(204), 'light');
check('cruiser conveyor class', Arsenal::conveyorClass(206), 'medium');
check('Flying Death conveyor class', Arsenal::conveyorClass(222), 'heavy');
check('particle emitter conveyor class', Arsenal::conveyorClass(419), 'heavy');
$convOwned = array(14 => array('value' => 0.6), 15 => array('value' => 0.5), 16 => array('value' => 0.4));
check('heavy conveyor upgrade pct', Arsenal::conveyorUpgradePct($convOwned, 228), 0.4);
check('light conveyor upgrade pct', Arsenal::conveyorUpgradePct($convOwned, 202), 0.6);

$weights = Expedition::outcomeWeights();
check('outcome weight sum', round(array_sum($weights), 10), 1.0);
check('combat weight', $weights[Expedition::OUTCOME_COMBAT], 0.135);
check('positive black hole weight', $weights[Expedition::OUTCOME_BLACKHOLE_LOOT], 0.017);
check('fatal black hole weight', $weights[Expedition::OUTCOME_BLACKHOLE], 0.0168);
check('first outcome', Expedition::rollOutcome(0.0), Expedition::OUTCOME_RESOURCES);
check('resource boundary', Expedition::rollOutcome(0.17), Expedition::OUTCOME_RESOURCES);
check('ship bucket', Expedition::rollOutcome(0.20), Expedition::OUTCOME_SHIPS);
check('last outcome', Expedition::rollOutcome(0.999999), Expedition::OUTCOME_STARDUST);

check('pirates below split', Expedition::rollNPC(0.839), Expedition::NPC_PIRATES);
check('aliens at split', Expedition::rollNPC(0.84), Expedition::NPC_ALIENS);

$template = Expedition::template();
check('template heavy cargo min', $template[203][0], 20);
check('template heavy cargo max', $template[203][1], 180);
check('template black moon alien only', $template[216][2], TRUE);
check('mirrored research max', Expedition::mirroredResearchBonus(10, 5, 0), 30);

list($enemy, $bonus) = Expedition::enemyComposition(array(204 => 100), Expedition::NPC_PIRATES, 0.0, 0.0, 30);
check('pirate mirror 60%', $enemy[204] >= 60, TRUE);
check('pirate template added', $enemy[207] >= 4, TRUE);
check('pirate strength floor', round($bonus, 6), 3.0);
list($aliens, $alienBonus) = Expedition::enemyComposition(array(204 => 100), Expedition::NPC_ALIENS, 1.0, 1.0, 30);
check('alien mirror 94%', $aliens[204] >= 94 && $aliens[204] <= 184, TRUE);
check('alien strength ceiling', round($alienBonus, 6), 75.0);

$loot = Expedition::loot(1000, 0.0);
check('loot metal', $loot[901], 165);
check('loot crystal', $loot[902], 105);
check('loot deuterium', $loot[903], 30);
check('loot zero capacity', array_sum(Expedition::loot(0, 0.5)), 0);

$bh = Expedition::blackHoleLoot(1000, 1.0, 1.0);
check('positive black hole capped', array_sum($bh), 1000);

check('DM floor', Expedition::darkMatter(1000000, 0.0), 100);
check('DM low scale', Expedition::darkMatter(1000000000000, 0.0), 3300);
check('DM high scale', Expedition::darkMatter(1000000000000, 1.0), 15000);

$migration = file_get_contents(__DIR__ . '/../install/migrations/migration_10.sql');
foreach (array(71, 72, 73, 221, 222, 225, 226, 227, 228, 412, 413, 414, 415, 416, 417, 418, 419) as $id) {
    check('migration adds '.$id, strpos($migration, '('.$id.',') !== FALSE, TRUE);
}
check('migration has rapidfire', strpos($migration, 'vars_rapidfire') !== FALSE, TRUE);
check('migration has conveyor requirements', strpos($migration, '(71,15,5)') !== FALSE, TRUE);

$proc = Academy::procPercents(array(
    '1103' => 5, '1109' => 5, '1108' => 5, '1110' => 5, '1111' => 5,
    '1303' => 5, '1311' => 5, '1308' => 5,
));
check('proc extra shots', $proc['extra_shots'], 15);
check('proc extra shot damage', $proc['extra_shot_damage'], 40);
check('proc damage', $proc['damage'], 15);
check('proc shield', $proc['shield'], 25);
check('proc rf reduction', $proc['rf_reduction'], 10);
check('proc empty', Academy::procPercents(array()), array(
    'extra_shots' => 0, 'extra_shot_damage' => 0, 'damage' => 0, 'shield' => 0, 'rf_reduction' => 0,
));

$procShip = new ShipType(207, 1, array(), 200, array(41000, 17000), 700, 0, 0, 0, 5800);
$procShip->applyAcademyProcs(15, 40, 15, 25, 10);
check('academy shield bonus', $procShip->getShield(), 250);
check('academy base power unchanged', $procShip->getPower(), 700);
check('academy damage getter', $procShip->getAcademyDamagePct(), 15);
check('academy extra shots getter', $procShip->getAcademyExtraShotsPct(), 15);
check('academy rf reduction getter', $procShip->getAcademyRfReductionPct(), 10);

$defFleet = new Fleet(1);
$defFleet->addShipType(new ShipType(204, 1, array(), 10, array(3000, 1000), 50, 0, 0, 0, 400));
$plainShip = new ShipType(204, 10, array(), 10, array(3000, 1000), 50, 0, 0, 0, 400);
$procFireShip = new ShipType(204, 10, array(), 10, array(3000, 1000), 50, 0, 0, 0, 400);
$procFireShip->applyAcademyProcs(15, 40, 15, 0, 0);
ob_start();
$plainFire = new Fire($plainShip, $defFleet);
$procFire = new Fire($procFireShip, $defFleet);
ob_end_clean();
check('fire plain shots', $plainFire->getAttackerTotalShots(), 10.0);
check('fire plain power', $plainFire->getAttackerTotalFire(), 500.0);
check('fire proc damage per shot', round($procFire->getPower(), 6), 57.5);
check('fire proc shots', round($procFire->getAttackerTotalShots(), 6), 11.5);
check('fire proc power', round($procFire->getAttackerTotalFire(), 6), 695.75);

check('diameter min gains', BlackMarket::diameterGains(0.0, 0.0), array(276, 12));
check('diameter max gains', BlackMarket::diameterGains(0.999999, 0.999999), array(414, 18));
check('diameter debris metal', BlackMarket::DIAMETER_METAL_DEBRIS, 100000000000);
check('diameter debris crystal', BlackMarket::DIAMETER_CRYSTAL_DEBRIS, 50000000000);

check('resource flavours', count(Expedition::flavourKeys(Expedition::OUTCOME_RESOURCES)), 9);
check('ship flavours', count(Expedition::flavourKeys(Expedition::OUTCOME_SHIPS)), 8);
check('dm flavours', count(Expedition::flavourKeys(Expedition::OUTCOME_DARKMATTER)), 10);
check('nothing flavours', count(Expedition::flavourKeys(Expedition::OUTCOME_NOTHING)), 9);
check('delay flavours', count(Expedition::flavourKeys(Expedition::OUTCOME_DELAY)), 6);
check('fast flavours', count(Expedition::flavourKeys(Expedition::OUTCOME_FAST)), 3);
check('black hole flavours', count(Expedition::flavourKeys(Expedition::OUTCOME_BLACKHOLE)), 4);
check('stardust first flavour', Expedition::flavour(Expedition::OUTCOME_STARDUST, 0.0), '1');
check('stardust last flavour', Expedition::flavour(Expedition::OUTCOME_STARDUST, 0.999), '4');
check('resource flavour roll', Expedition::flavour(Expedition::OUTCOME_RESOURCES, 0.999), '3_2');

$migration12 = file_get_contents(__DIR__ . '/../install/migrations/migration_12.sql');
check('migration adds stardust', strpos($migration12, '`stardust`') !== FALSE, TRUE);
$install = file_get_contents(__DIR__ . '/../install/install.sql');
check('install has stardust', strpos($install, '`stardust` bigint') !== FALSE, TRUE);
$fleetLang = file_get_contents(__DIR__ . '/../language/en/FLEET.php');
check('stardust flavour text', strpos($fleetLang, "sys_expe_stardust_4") !== FALSE, TRUE);

echo ($fail === 0 ? 'OK' : 'FAILED') . " - {$pass} passed, {$fail} failed\n";
exit($fail === 0 ? 0 : 1);
