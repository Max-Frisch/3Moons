<?php

/**
 * Focused, dependency-free checks for the Academy tree.
 * Run with: docker compose exec -T web php tests/academy_math.php
 */

require_once __DIR__ . '/../includes/classes/class.Academy.php';

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

$catalog = Academy::getCatalog();
check('catalog size', count($catalog), 34);

$branches = array(1 => 0, 2 => 0, 3 => 0);
foreach ($catalog as $skill) {
    $branches[$skill['branch']]++;
}
check('attack branch size', $branches[1], 12);
check('economy branch size', $branches[2], 11);
check('defence branch size', $branches[3], 11);

$anchors = array(
    array('1101', 19, 26),
    array('1102', 8, 9),
    array('1104', 17, 435),
    array('1105', 16, 85),
    array('1106', 1, 3),
    array('1107', 6, 11),
    array('1108', 7, 97),
    array('1110', 8, 119),
    array('1201', 19, 55),
    array('1203', 7, 19),
    array('1204', 6, 9),
    array('1308', 8, 214),
);
foreach ($anchors as $anchor) {
    check('cost ' . $anchor[0] . ' level ' . $anchor[1], Academy::cost($catalog[$anchor[0]], $anchor[1]), $anchor[2]);
}

check('tier of 1101', $catalog['1101']['tier'], 1);
check('tier of 1102', $catalog['1102']['tier'], 2);
check('tier of 1103', $catalog['1103']['tier'], 3);
check('tier of 1111', $catalog['1111']['tier'], 5);
check('tier of 1106', $catalog['1106']['tier'], 3);

check('prereq unmet', Academy::prereqMet($catalog['1102'], array('1101' => 2)), false);
check('prereq met', Academy::prereqMet($catalog['1102'], array('1101' => 3)), true);
check('root has no prereq', Academy::prereqMet($catalog['1201'], array()), true);

$w = $catalog['1101'];
check('affordable with no points', Academy::affordableLevel($w, 0, 10, 0), 0);
check('affordable all with plenty', Academy::affordableLevel($w, 0, 10, 1000000), 10);
$partial = Academy::affordableLevel($w, 0, 10, Academy::costRange($w, 0, 4));
check('affordable partial', $partial, 4);
check('cost range 0..4', Academy::costRange($w, 0, 4), Academy::cost($w, 1) + Academy::cost($w, 2) + Academy::cost($w, 3) + Academy::cost($w, 4));
check('cost range clamped to max', Academy::costRange($w, 0, 500), Academy::costRange($w, 0, $w['max_level']));

$levels = array(
    '1101' => 10,
    '1211' => 5,
    '1201' => 10,
    '1202' => 10,
    '1203' => 10,
    '1204' => 10,
    '1207' => 10,
    '1104' => 5,
    '1105' => 5,
    '1107' => 5,
    '1106' => 5,
    '1206' => 3,
    '1205' => 2,
);
check('attack pct', Academy::attackPct($levels), 15);
check('shield pct', Academy::shieldPct($levels), 5);
check('hull pct', Academy::hullPct($levels), 5);
check('mine pct', Academy::minePct($levels), 50);
check('energy pct', Academy::energyPct($levels), 20);
check('research pct', Academy::researchPct($levels), 20);
check('storage pct', Academy::storagePct($levels), 50);
check('cargo pct', Academy::cargoPct($levels), 50);
check('fleet cost pct', Academy::fleetCostPct($levels), 5);
check('fleet speed pct', Academy::fleetSpeedPct($levels), 20);
check('fuel pct', Academy::fuelPct($levels), 10);
check('fleet slots', Academy::fleetSlots($levels), 5);
check('irn', Academy::irn($levels), 2);
check('planets', Academy::planets($levels), 3);

$USER = array('id' => 1, 'academy_levels' => $levels);
$factor = array(
    'Attack' => 0, 'Shield' => 0, 'Defensive' => 0, 'Resource' => 0,
    'Energy' => 0, 'ResearchTime' => 0, 'ResourceStorage' => 0,
    'ShipStorage' => 0, 'FlyTime' => 0, 'FleetSlots' => 0, 'Planets' => 0,
);
$factor = Academy::applyFactors($USER, $factor);
check('factor attack', $factor['Attack'], 0.15);
check('factor shield', $factor['Shield'], 0.05);
check('factor hull', $factor['Defensive'], 0.05);
check('factor mine', $factor['Resource'], 0.50);
check('factor research', $factor['ResearchTime'], -0.20);
check('factor cargo', $factor['ShipStorage'], 0.50);
check('factor speed', $factor['FlyTime'], 0.20);
check('factor slots', $factor['FleetSlots'], 5);
check('factor planets', $factor['Planets'], 3);
check('fleet cost reduction wrapper', Academy::fleetCostReductionPct($USER), 5);
check('fuel reduction wrapper', Academy::fuelReductionPct($USER), 10);
check('irn wrapper', Academy::irnLevels($USER), 2);

echo "pass={$pass} fail={$fail}\n";
exit($fail === 0 ? 0 : 1);
