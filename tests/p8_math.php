<?php

/**
 * Focused checks for the P8 report summary helpers.
 * Run with: docker compose exec -T web php tests/p8_math.php
 */

require_once __DIR__ . '/../includes/classes/class.CombatReport.php';
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

$report = array(
    'result' => 'a',
    'rounds' => array(
        array(
            'attacker' => array(
                array('userID' => 1, 'ships' => array(204 => array(10, 0, 0, 0), 205 => array(5, 0, 0, 0))),
            ),
            'defender' => array(
                array('userID' => 2, 'ships' => array(401 => array(20, 0, 0, 0))),
            ),
        ),
        array(
            'attacker' => array(
                array('userID' => 1, 'ships' => array(204 => array(6, 0, 0, 0))),
            ),
            'defender' => array(
                array('userID' => 2, 'ships' => array()),
            ),
        ),
    ),
);

$summary = CombatReport::summarize($report);

check('attacker start total', $summary['attacker']['startTotal'], 15);
check('attacker lost total', $summary['attacker']['lostTotal'], 9);
check('attacker left total', $summary['attacker']['leftTotal'], 6);
check('attacker 204 lost', $summary['attacker']['lost'][204], 4);
check('attacker 205 lost', $summary['attacker']['lost'][205], 5);
check('attacker 205 left', $summary['attacker']['left'][205], 0);
check('defender start total', $summary['defender']['startTotal'], 20);
check('defender lost total', $summary['defender']['lostTotal'], 20);
check('defender left total', $summary['defender']['leftTotal'], 0);

$share = CombatReport::lossShare($summary);
check('attacker loss share', $share['attacker'], 31.03);
check('defender loss share', $share['defender'], 68.97);

check('winner attacker', CombatReport::winner('a'), 'attacker');
check('winner defender', CombatReport::winner('r'), 'defender');
check('winner draw', CombatReport::winner('w'), 'draw');
check('winner unknown', CombatReport::winner(''), 'draw');

$multi = array(
    'rounds' => array(
        array(
            'attacker' => array(
                array('userID' => 1, 'ships' => array(204 => array(3, 0, 0, 0))),
                array('userID' => 2, 'ships' => array(204 => array(2, 0, 0, 0), 205 => array(1, 0, 0, 0))),
            ),
            'defender' => array(),
        ),
    ),
);
$multiSummary = CombatReport::summarize($multi);
check('multi-player same ship', $multiSummary['attacker']['start'][204], 5);
check('multi-player second ship', $multiSummary['attacker']['start'][205], 1);
check('multi-player no losses', $multiSummary['attacker']['lostTotal'], 0);

$guarded = CombatReport::ensurePlayers($report);
check('ensurePlayers fills attacker', $guarded['players'][1]['name'], 'Unknown');
check('ensurePlayers fills defender', $guarded['players'][2]['name'], 'Unknown');
check('ensurePlayers keeps rounds', count($guarded['rounds']), 2);

$empty = CombatReport::summarize(array());
check('empty attacker total', $empty['attacker']['startTotal'], 0);
check('empty defender total', $empty['defender']['lostTotal'], 0);

$emptyShare = CombatReport::lossShare($empty);
check('empty share does not divide by zero', $emptyShare['attacker'], 0);
check('empty share defender', $emptyShare['defender'], 0);

check('expedition report class', Expedition::reportClass(Expedition::OUTCOME_BLACKHOLE_LOOT), 'exp-report-blackhole-loot');
check('expedition report class sanitised', Expedition::reportClass('Odd Value!'), 'exp-report-oddvalue');

echo ($fail === 0 ? 'OK' : 'FAILED') . " - {$pass} passed, {$fail} failed\n";
exit($fail === 0 ? 0 : 1);
