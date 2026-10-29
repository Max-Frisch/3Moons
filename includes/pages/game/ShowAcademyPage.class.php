<?php

/**
 * Niburuspace-style Academy page.
 *
 * The tree and point costs are ported from the captured live tree; see
 * includes/classes/class.Academy.php for sources.
 */
class ShowAcademyPage extends AbstractGamePage
{
    public static $requireModule = 0;

    public function __construct()
    {
        parent::__construct();
    }

    private function setNotice($notice)
    {
        $_SESSION['academyNotice'] = $notice;
    }

    private function getNotice()
    {
        if (empty($_SESSION['academyNotice'])) {
            return NULL;
        }
        $notice = $_SESSION['academyNotice'];
        unset($_SESSION['academyNotice']);
        return $notice;
    }

    private function currentEffect($skill, $level)
    {
        global $LNG;

        if ($level <= 0) {
            return '';
        }

        $labels = array(
            'attack' => 'acad_attack',
            'shield' => 'acad_shield',
            'hull' => 'acad_hull',
            'mine' => 'acad_mine',
            'energy' => 'acad_energy',
            'research' => 'acad_research',
            'storage' => 'acad_storage',
            'cargo' => 'acad_cargo',
            'fleet_cost' => 'acad_fleet_cost',
            'fleet_speed' => 'acad_fleet_speed',
            'fuel' => 'acad_fuel',
            'fleet_slot' => 'acad_fleet_slot',
            'irn' => 'acad_irn',
            'planets' => 'acad_planets',
            'double_shield' => 'acad_double_shield',
            'rf_reduction' => 'acad_rf',
        );

        $parts = array();
        foreach ($labels as $key => $label) {
            if (empty($skill['effect'][$key])) {
                continue;
            }
            $value = $level * $skill['effect'][$key];
            if ($key === 'fleet_slot' || $key === 'irn' || $key === 'planets') {
                $parts[] = sprintf($LNG['acad_effect_count'], $value > 0 ? '+' . $value : (string)$value, $LNG[$label]);
            } else {
                $parts[] = sprintf($LNG['acad_effect_pct'], $value > 0 ? '+' . $value : (string)$value, $LNG[$label]);
            }
        }

        return implode(', ', $parts);
    }

    private function buildBranches($levels)
    {
        global $LNG;

        $catalog = Academy::getCatalog();
        $points = Academy::getPoints($GLOBALS['USER']);

        $branches = array();
        foreach (Academy::getBranches() as $branchID => $titleKey) {
            $skills = array();
            foreach ($catalog as $code => $skill) {
                if ($skill['branch'] !== $branchID) {
                    continue;
                }

                $level = isset($levels[$code]) ? (int)$levels[$code] : 0;
                $maxed = $level >= $skill['max_level'];
                $prereqMet = Academy::prereqMet($skill, $levels);
                $cost = $maxed ? 0 : Academy::cost($skill, $level + 1);

                $prereq = array();
                foreach ($skill['prereq'] as $reqCode => $reqLevel) {
                    $reqSkill = Academy::getSkill($reqCode);
                    $prereq[] = sprintf(
                        $LNG['acad_prereq_line'],
                        $reqSkill !== NULL ? $reqSkill['name'] : $reqCode,
                        $reqLevel,
                        isset($levels[$reqCode]) ? (int)$levels[$reqCode] : 0
                    );
                }

                $skills[] = array(
                    'code' => $code,
                    'name' => $skill['name'],
                    'level' => $level,
                    'maxLevel' => $skill['max_level'],
                    'tier' => $skill['tier'],
                    'tierRoman' => $this->roman($skill['tier']),
                    'effectText' => $skill['effect_text'],
                    'currentEffect' => $this->currentEffect($skill, $level),
                    'cost' => $cost,
                    'prereq' => $prereq,
                    'prereqMet' => $prereqMet,
                    'maxed' => $maxed,
                    'buyable' => !$maxed && $prereqMet && $points >= $cost,
                    'nextLevel' => min($level + 1, $skill['max_level']),
                );
            }

            usort($skills, function ($a, $b) {
                if ($a['tier'] === $b['tier']) {
                    return strcmp($a['code'], $b['code']);
                }
                return $a['tier'] - $b['tier'];
            });

            $branches[$branchID] = array(
                'title' => $LNG[$titleKey],
                'skills' => $skills,
            );
        }

        return $branches;
    }

    private function roman($number)
    {
        $map = array(1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V');
        return isset($map[$number]) ? $map[$number] : (string)$number;
    }

    public function show()
    {
        global $USER, $LNG;

        $action = HTTP::_GP('action', '');
        if ($action !== '' && $_SERVER['REQUEST_METHOD'] === 'POST' && $USER['urlaubs_modus'] == 0) {
            if ($action === 'up') {
                $result = Academy::upgrade($USER, HTTP::_GP('skill', ''), HTTP::_GP('target', 0));
                if ($result['ok']) {
                    $this->setNotice(array(
                        'type' => 'ok',
                        'level' => $result['level'],
                        'spent' => $result['spent'],
                        'partial' => $result['partial'],
                        'requested' => $result['requested'],
                    ));
                } else {
                    $this->setNotice(array('type' => 'error', 'reason' => $result['reason']));
                }
            } elseif ($action === 'reset') {
                $result = Academy::resetBranch($USER, HTTP::_GP('branch', 0), HTTP::_GP('percent', 0));
                if ($result['ok']) {
                    $this->setNotice(array(
                        'type' => 'reset',
                        'refunded' => $result['refunded'],
                        'percent' => $result['percent'],
                    ));
                } else {
                    $this->setNotice(array('type' => 'error', 'reason' => $result['reason']));
                }
            }

            $this->redirectTo('game.php?page=academy');
        }

        $levels = Academy::getLevels($USER['id']);
        $branches = $this->buildBranches($levels);

        $this->assign(array(
            'academyPoints' => pretty_number(Academy::getPoints($USER)),
            'academyPointsSpent' => pretty_number(isset($USER['academy_points_spent']) ? $USER['academy_points_spent'] : 0),
            'academyBranches' => $branches,
            'academyNotice' => $this->getNotice(),
            'resetFullCost' => pretty_number(Academy::RESET_ANTIMATTER_COST),
            'resetPartialCost' => pretty_number(Academy::RESET_DARKMATTER_COST),
        ));

        $this->display('page.academy.default.tpl');
    }
}
