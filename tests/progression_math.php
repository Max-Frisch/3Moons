<?php

/**
 * Focused, dependency-free checks for peaceful level, combat XP/battle level,
 * the Online Bonus payout and the premium cost model.
 * Run with: docker compose exec -T web php tests/progression_math.php
 */

require_once __DIR__ . '/../includes/classes/class.Progression.php';

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

function checkClose($label, $actual, $expected, $tolerance)
{
    global $fail, $pass;
    if (abs($actual - $expected) <= $tolerance) {
        $pass++;
        return;
    }
    $fail++;
    echo "FAIL {$label}: got " . var_export($actual, true) . ', expected ' . var_export($expected, true) . "\n";
}

$durations = array(3 => 4210, 4 => 4460, 5 => 4705, 76 => 210600, 77 => 222000);
foreach ($durations as $level => $captured) {
    $seconds = Progression::peacefulLevelSecondsRaw($level);
    checkClose('peaceful duration L' . $level, $seconds, $captured, $captured * 0.02);
}

$state = Progression::peacefulState(0);
check('state at zero level', $state['level'], 0);
check('state at zero progress', $state['progress'], 0.0);

$seconds = Progression::cumulativePeacefulSeconds(10);
$state = Progression::peacefulState($seconds);
check('state at cumulative level', $state['level'], 10);
checkClose('state at cumulative progress', $state['progress'], 0.0, 0.0001);

$half = $seconds + Progression::peacefulLevelSecondsRaw(11) / 2;
$state = Progression::peacefulState($half);
check('state mid level', $state['level'], 10);
checkClose('state mid progress', $state['progress'], 50.0, 0.5);

check('academy points at L3', Progression::academyPointsBase(3), 1.0);
check('academy points at L77', Progression::academyPointsBase(77), 8.5);

$reward = Progression::bonusRewardForLevel(3, 1, 0);
check('bonus DM L3', $reward['darkmatter'], 8890);
check('bonus AP L3', $reward['academy_points'], 1);

$reward = Progression::bonusRewardForLevel(77, 4, 100);
check('premium bonus AP L77', $reward['academy_points'], 34);
check('premium bonus DM L77', $reward['darkmatter'], 222040);
check('premium bonus exp L77', $reward['experience_pct'], 20.0);

$bonus = Progression::peacefulBonuses(76);
check('mines at L76', $bonus['mines'], 76);
check('fleet slots at L76', $bonus['fleet_slots'], 7);
check('planet fields at L76', $bonus['fields_planet'], 15);
check('moon fields at L76', $bonus['fields_moon'], 6);
check('small factory at L76', $bonus['small_factory'], 7);
check('medium factory at L76', $bonus['medium_factory'], 3);
check('large factory at L76', $bonus['large_factory'], 1);

$bonus = Progression::peacefulBonuses(3);
check('fleet slots at L3', $bonus['fleet_slots'], 0);
check('planet fields at L3', $bonus['fields_planet'], 0);

$thresholds = array(
    0 => 10700,
    1 => 11449,
    2 => 12250,
    16 => 31588,
    39 => 149745,
    47 => 257289,
    59 => 579464,
    60 => 620027,
    61 => 663429,
);
foreach ($thresholds as $level => $expected) {
    check('battle threshold L' . $level, Progression::battleThreshold($level), $expected);
}

$battle = Progression::battleState(0);
check('battle start level', $battle['level'], 0);
check('battle start xp', $battle['xp'], 0);
check('battle start next', $battle['next_xp'], 10700);

$battle = Progression::battleState(10700 + 11449 + 5);
check('battle level after two thresholds', $battle['level'], 2);
check('battle xp within level', $battle['xp'], 5);
check('battle next at level 2', $battle['next_xp'], 12250);

check('combat xp one unit', Progression::combatXpForLoss(635000000), 1);
check('combat xp two units', Progression::combatXpForLoss(1270000000), 2);
check('combat xp zero', Progression::combatXpForLoss(0), 0);
check('combat xp 500b', Progression::combatXpForLoss(500000000000), 787);
check('combat xp premium +55%', Progression::combatXpForLoss(635000000, 55), 2);

$lost = array(
    1 => array(11 => array(204 => array(100, 50))),
    2 => array(12 => array(213 => array(200, 100))),
);
check('lost resources recursive sum', Progression::lostResources($lost), 450);
check('lost resources scalar', Progression::lostResources(500000000000), 500000000000);
check('lost resources invalid', Progression::lostResources('x'), 0);

$catalog = Progression::getPremiumCatalog();
check('premium catalog size', count($catalog), 19);
check('resource bonus is extend-only', $catalog['prem_res']['kind'], 'ext');
check('resource fixed amount', $catalog['prem_res']['fixed'], 1000);
check('button minimum', $catalog['prem_button']['min'], 2);
check('button maximum', $catalog['prem_button']['max'], 10);

check('premium cost storage', Progression::premiumCost('prem_storage', 1, 1), 1);
check('premium cost button x2', Progression::premiumCost('prem_button', 2, 1), 1045);
check('premium cost resource day', Progression::premiumCost('prem_res', 1000, 1), 60736);
check('premium cost build speed day', Progression::premiumCost('prem_s_build', 100, 1), 8621);
check('premium cost leveling day', Progression::premiumCost('prem_leveling', 100, 1), 4332);
check('premium cost combat xp', Progression::premiumCost('prem_batle_leveling', 55, 1), 2362);
check('premium cost 200 days discount', Progression::premiumCost('prem_storage', 1, 200), 100);
check('premium cost unknown', Progression::premiumCost('nope', 1, 1), 0);

echo "pass={$pass} fail={$fail}\n";
exit($fail === 0 ? 0 : 1);
