<?php

/**
 * Checks that the target-level UI sum matches the server price curve.
 * Run with: docker compose exec -T web php tests/target_level_math.php
 */

require_once __DIR__ . '/../includes/classes/class.BuildFunctions.php';

$fail = 0;
$pass = 0;

function check($label, $actual, $expected)
{
    global $fail, $pass;
    if (abs($actual - $expected) < 0.0001) {
        $pass++;
        return;
    }
    $fail++;
    echo "FAIL {$label}: got " . var_export($actual, true) . ', expected ' . var_export($expected, true) . "\n";
}

function targetTotal($element, $start, $target)
{
    global $USER, $PLANET;

    $total = 0;
    for ($level = $start; $level <= $target; $level++) {
        $price = BuildFunctions::getElementPrice($USER, $PLANET, $element, false, $level);
        $total += $price[901];
    }
    return $total;
}

$pricelist = array(
    1 => array('cost' => array(901 => 60), 'factor' => 1.5),
    2 => array('cost' => array(901 => 100)),
);
$resource = array(901 => 'metal', 902 => 'crystal');
$reslist = array(
    'ressources' => array(901, 902),
    'fleet' => array(),
    'defense' => array(),
    'missile' => array(),
    'build' => array(1, 2),
);
$USER = array();
$PLANET = array();

$level1 = BuildFunctions::getElementPrice($USER, $PLANET, 1, false, 1);
$level3 = BuildFunctions::getElementPrice($USER, $PLANET, 1, false, 3);
check('curve level 1', $level1[901], 90);
check('curve level 3', $level3[901], 202.5);
check('curve total 1-3', targetTotal(1, 1, 3), 427.5);

$flat1 = BuildFunctions::getElementPrice($USER, $PLANET, 2, false, 1);
$flat3 = BuildFunctions::getElementPrice($USER, $PLANET, 2, false, 3);
check('flat level 1', $flat1[901], 100);
check('flat total 1-3', targetTotal(2, 1, 3), 300);

echo ($fail === 0 ? 'OK' : 'FAILED') . " - {$pass} passed, {$fail} failed\n";
exit($fail === 0 ? 0 : 1);
