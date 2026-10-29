<?php

/**
 * Focused, dependency-free checks for black market / Planetarium math.
 * Run with: docker compose exec -T web php tests/blackmarket_math.php
 */

require_once __DIR__ . '/../includes/classes/class.BlackMarket.php';

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

check('merchant rate clamp low', BlackMarket::merchantRate(-5), 0);
check('merchant rate clamp high', BlackMarket::merchantRate(140), 100);
check('merchant rate live', BlackMarket::merchantRate(50), 50);

$base = array(901 => 1000, 902 => 500, 903 => 250, 921 => 7);
$refund = BlackMarket::merchantRefund($base, 50);
check('refund metal', $refund[901], 500);
check('refund crystal', $refund[902], 250);
check('refund deuterium', $refund[903], 125);
check('refund dark matter', $refund[921], 3);
check('refund zero rate', BlackMarket::merchantRefund($base, 0), array());

check('dm price live divisor', BlackMarket::dmMarketPrice(array(901 => 600, 902 => 300, 903 => 100)), 1);
check('dm price ceil', BlackMarket::dmMarketPrice(array(901 => 600, 902 => 401)), 2);
check('dm price zero cost', BlackMarket::dmMarketPrice(array(901 => 0)), 0);
check('dm price bad divisor', BlackMarket::dmMarketPrice(array(901 => 10), 0), 10);

check('field +1', BlackMarket::fieldExpansionCost(0, 1), 200);
check('field +2', BlackMarket::fieldExpansionCost(0, 2), 420);
check('field +3', BlackMarket::fieldExpansionCost(0, 3), 662);
check('field +4', BlackMarket::fieldExpansionCost(0, 4), 928);
check('field +5', BlackMarket::fieldExpansionCost(0, 5), 1221);
check('field +6', BlackMarket::fieldExpansionCost(0, 6), 1543);
check('field one more after six', BlackMarket::fieldExpansionCost(6, 1), 354);
check('field no-op', BlackMarket::fieldExpansionCost(4, 0), 0);

check('reloc position', BlackMarket::relocationCost(3, 125, 12, 3, 125, 13), 2500);
check('reloc system', BlackMarket::relocationCost(3, 125, 12, 3, 126, 12), 1000);
check('reloc mixed', BlackMarket::relocationCost(3, 125, 12, 3, 127, 14), 7000);
check('reloc galaxy', BlackMarket::relocationCost(3, 125, 12, 2, 125, 12), 15000);
check('reloc galaxy+system+pos', BlackMarket::relocationCost(3, 125, 12, 2, 127, 14), 22000);
check('reloc same coords', BlackMarket::relocationCost(3, 125, 12, 3, 125, 12), 0);

check('reloc valid target', BlackMarket::validRelocationTarget(3, 125, 12, 9, 499, 20), true);
check('reloc invalid galaxy', BlackMarket::validRelocationTarget(10, 125, 12, 9, 499, 20), false);
check('reloc invalid system', BlackMarket::validRelocationTarget(3, 500, 12, 9, 499, 20), false);
check('reloc invalid position', BlackMarket::validRelocationTarget(3, 125, 21, 9, 499, 20), false);

check('reloc leaves system', BlackMarket::relocationLeavesSystem(3, 125, 3, 126), true);
check('reloc same system', BlackMarket::relocationLeavesSystem(3, 125, 3, 125), false);
check('same coordinates', BlackMarket::sameCoordinates(3, 125, 12, 3, 125, 12), true);
check('different coordinates', BlackMarket::sameCoordinates(3, 125, 12, 3, 125, 13), false);

echo ($fail === 0 ? 'OK' : 'FAILED') . " - {$pass} passed, {$fail} failed\n";
exit($fail === 0 ? 0 : 1);
