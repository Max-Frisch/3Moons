<?php

/**
 * Focused checks for the Moon Collider cost/production curves.
 * Run with: docker compose exec -T web php tests/collider_math.php
 */

require_once __DIR__ . '/../includes/classes/class.Collider.php';

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

check('cost level 1', Collider::cost(1), 850);
check('cost level 2', Collider::cost(2), 1148);
check('cost level 3', Collider::cost(3), 1549);
check('cost below level 1 clamps', Collider::cost(0), 850);
check('cost level 6', Collider::cost(6), 3811);

check('production level 0', Collider::production(0), 0);
check('production level 1', Collider::production(1), 4752);
check('production level 2', Collider::production(2), 10454);
check('production level 3', Collider::production(3), 17250);
check('production level 4 extrapolated', Collider::production(4), 25140);

check('factor unmodified', Collider::factor(0, 0), 1.0);
check('factor battle level 10', Collider::factor(10, 0), 1.3);
check('factor premium 100', Collider::factor(0, 100), 2.0);
check('factor combined', round(Collider::factor(10, 50), 4), 1.95);

check('production for level 2', Collider::productionFor(2, 0, 0), 10454);
check('production with premium', Collider::productionFor(2, 0, 100), 20908);
check('production level 0 multiplier', Collider::productionFor(0, 10, 100), 0);

echo ($fail === 0 ? 'OK' : 'FAILED') . " - {$pass} passed, {$fail} failed\n";
exit($fail === 0 ? 0 : 1);
