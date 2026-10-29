<?php

/**
 * Focused, dependency-free checks for Arsenal catalog, tiers, activation and
 * unit-class bonus wiring.
 * Run with: docker compose exec -T web php tests/arsenal_math.php
 */

require_once __DIR__ . '/../includes/classes/class.Arsenal.php';

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

$catalog = Arsenal::getCatalog();
check('catalog size', count($catalog), 19);
check('laser weapons per level', $catalog[1]['per_level'], 0.75);
check('crystal production per level', $catalog[18]['per_level'], 0.45);
check('jet engine key', $catalog[11]['key'], 'combustion');
check('light conveyor key', $catalog[14]['key'], 'l_conveyer');
check('production group', $catalog[19]['group'], 'production');

$byKey = Arsenal::getUpgradeByKey('combustion');
check('lookup by greid', $byKey['code'], 11);
check('lookup unknown greid', Arsenal::getUpgradeByKey('nope'), NULL);
check('lookup unknown code', Arsenal::getUpgrade(99), NULL);

check('tier 0 below 5k', Arsenal::tier(4999), 0);
check('tier 1 at 5k', Arsenal::tier(5000), 1);
check('tier 2 at 50k', Arsenal::tier(50000), 2);
check('tier 3 at 250k', Arsenal::tier(250000), 3);
check('tier min 2', Arsenal::tierMinPoints(2), 50000);

check('tier 0 pool', count(Arsenal::dropPool(0)), 0);
check('tier 1 pool', count(Arsenal::dropPool(1)), 8);
check('tier 2 pool', count(Arsenal::dropPool(2)), 14);
check('tier 3 pool', count(Arsenal::dropPool(3)), 19);

check('drop blocked at chance threshold', Arsenal::rollDrop(1, 0.10, 0), 0);
check('drop picked first', Arsenal::rollDrop(1, 0, 0), 1);
check('drop picked last at tier 3', Arsenal::rollDrop(3, 0, 0.999999), 16);
check('drop tier 0 yields none', Arsenal::rollDrop(0, 0, 0), 0);

check('chance level 0', Arsenal::activationChance(0), 1.0);
check('chance level 9', Arsenal::activationChance(9), 1.0);
check('chance level 10', Arsenal::activationChance(10), 0.98);
check('chance level 11', Arsenal::activationChance(11), 0.96);
check('chance floor', Arsenal::activationChance(22), 0.75);

$def = $catalog[1];
$success = Arsenal::activate($def, 0, 0, 0);
check('activation success level', $success['level'], 1);
check('activation success value', $success['value'], 0.75);
check('activation success flag', $success['success'], true);

$failure = Arsenal::activate($def, 10, 3.0, 0.99);
check('activation failure keeps level', $failure['level'], 10);
check('activation failure shaves value', $failure['value'], 2.925);
check('activation failure flag', $failure['success'], false);

$owned = array(
    1 => array('level' => 1, 'value' => 0.75),
    5 => array('level' => 2, 'value' => 1.2),
    8 => array('level' => 2, 'value' => 0.9),
    11 => array('level' => 1, 'value' => 0.6),
    17 => array('level' => 1, 'value' => 0.5),
    18 => array('level' => 1, 'value' => 0.45),
    19 => array('level' => 1, 'value' => 0.4),
);

$lightFighter = Arsenal::combatPercents($owned, 204);
check('standard weapon gets no Arsenal attack', $lightFighter['Attack'], 0);
check('light armour bonus', $lightFighter['Defensive'], 1.2);
check('light shield bonus', $lightFighter['Shield'], 0.9);

$battleship = Arsenal::combatPercents($owned, 207);
check('laser weapon bonus', $battleship['Attack'], 0.75);
check('medium armour without upgrade', $battleship['Defensive'], 0);
check('medium shield without upgrade', $battleship['Shield'], 0);

check('unknown unit has no bonus', Arsenal::combatPercents($owned, 999), array('Attack' => 0.0, 'Shield' => 0.0, 'Defensive' => 0.0));
check('combustion engine bonus', Arsenal::enginePercent($owned, 204), 0.6);
check('hyperspace engine without upgrade', Arsenal::enginePercent($owned, 207), 0);
check('metal production bonus', Arsenal::productionPercent($owned, 901), 0.5);
check('crystal production bonus', Arsenal::productionPercent($owned, 902), 0.45);
check('deuterium production bonus', Arsenal::productionPercent($owned, 903), 0.4);
check('non-production resource', Arsenal::productionPercent($owned, 911), 0);
check('production upgrade predicate', Arsenal::isProductionUpgrade(18), true);
check('non-production upgrade predicate', Arsenal::isProductionUpgrade(11), false);

echo "pass={$pass} fail={$fail}\n";
exit($fail === 0 ? 0 : 1);
