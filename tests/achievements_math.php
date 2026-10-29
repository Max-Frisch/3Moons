<?php

/**
 * Focused, dependency-free checks for the achievement / governator math.
 * Run with: docker compose exec -T web php tests/achievements_math.php
 */

require_once __DIR__ . '/../includes/classes/class.Achievements.php';

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

$catalog = Achievements::getCatalog();
check('catalog size', count($catalog), 47);

$metal = $catalog['metal_miner'];
check('metal level at mine 51', Achievements::levelFor($metal, 51), 17);
check('metal next requirement after 16', Achievements::nextRequirement($metal, 16), 51);
check('metal AP reward level 17', Achievements::reward($metal['ap'], 17), 121);

$terra = $catalog['terraformer'];
check('terraformer cumulative AP at level 11', Achievements::earnedFor($terra, 11), 343);

$fighters = $catalog['fleet_fighters'];
$fleetReq = 200000 * pow(1.2509, 39);
check('fleet fighters level at captured requirement', Achievements::levelFor($fighters, $fleetReq), 40);
check('fleet fighters below first requirement', Achievements::levelFor($fighters, 199999), 0);

check('light defense below first requirement', Achievements::levelFor($catalog['light_defense'], 199999), 0);
check('light defense at first requirement', Achievements::levelFor($catalog['light_defense'], 200000), 1);

check('governator 701 AP level 0', Achievements::governatorAPCost(701, 0), 50);
check('governator 701 AP level 1', Achievements::governatorAPCost(701, 1), 54);
check('governator 701 DM level 0', Achievements::governatorDMPrice(701, 0), 40000);
check('governator 701 DM level 1', Achievements::governatorDMPrice(701, 1), 44000);

$pricelist = array(
    703 => array('bonus' => array('BuildTime' => array(-0.10, 0.0))),
);
check('building governator at level 50', Achievements::governatorBonus(703, 50)['BuildTime'], -60);

echo "pass={$pass} fail={$fail}\n";
exit($fail === 0 ? 0 : 1);
