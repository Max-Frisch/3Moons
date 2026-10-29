<?php

/**
 *  2Moons
 *   by Jan-Otto Kröpke 2009-2016
 *
 * For the full copyright and license information, please view the LICENSE
 *
 * @package 2Moons
 * @author Jan-Otto Kröpke <slaver7@gmail.com>
 * @copyright 2009 Lucky
 * @copyright 2016 Jan-Otto Kröpke <slaver7@gmail.com>
 * @licence MIT
 * @version 1.8.0
 * @link https://github.com/jkroepke/2Moons
 */

class ShowAchievementsPage extends AbstractGamePage
{
    public static $requireModule = 0;

    function __construct()
    {
        parent::__construct();
    }

    private function requirementLines($def, $level)
    {
        global $LNG;

        $next = Achievements::nextRequirement($def, $level);
        $lines = array();

        if (isset($def['source']['building']) || isset($def['source']['tech'])) {
            $ids = isset($def['source']['building']) ? $def['source']['building'] : $def['source']['tech'];
            foreach ($ids as $id) {
                $lines[] = sprintf($LNG['ach_req_level'], Achievements::sourceLabel($id), pretty_number($next));
            }
            return $lines;
        }

        if (isset($def['source']['unit'])) {
            foreach ($def['source']['unit'] as $id) {
                $lines[] = sprintf($LNG['ach_req_unit'], Achievements::sourceLabel($id), pretty_number($next));
            }
            return $lines;
        }

        $key = 'ach_req_' . $def['key'];
        $lines[] = isset($LNG[$key]) ? sprintf($LNG[$key], pretty_number($next)) : pretty_number($next);
        return $lines;
    }

    public function show()
    {
        global $USER, $LNG;

        $groups = Achievements::getGroups();
        $group = HTTP::_GP('group', 'all');
        if (!isset($groups[$group])) {
            $group = 'all';
        }

        $catalog = Achievements::getCatalog();
        $levels = Achievements::getLevels($USER['id']);
        $stats = Achievements::collectStats($USER);

        $totalEarned = 0;
        foreach ($catalog as $key => $def) {
            $level = isset($levels[$key]['level']) ? (int)$levels[$key]['level'] : 0;
            $totalEarned += Achievements::earnedFor($def, $level);
        }

        $groupList = array();
        foreach ($groups as $groupKey => $languageKey) {
            $groupList[$groupKey] = array(
                'title'  => $LNG[$languageKey],
                'active' => $groupKey === $group,
                'url'    => 'game.php?page=achievements&amp;group=' . $groupKey,
            );
        }

        $achievementList = array();
        foreach ($catalog as $key => $def) {
            if ($def['group'] !== $group) {
                continue;
            }

            $level = isset($levels[$key]['level']) ? (int)$levels[$key]['level'] : 0;
            $metric = Achievements::metricFor($def, $stats);
            $nextLevel = $level + 1;
            $requirement = $this->requirementLines($def, $level);

            $icon = (int)$def['icon'];
            if ($icon > 0 && !file_exists(ROOT_PATH . 'styles/theme/' . $USER['dpath'] . '/gebaeude/' . $icon . '.gif')) {
                $icon = 0;
            }

            $achievementList[] = array(
                'name'        => $def['name'],
                'level'       => $level,
                'description' => isset($def['desc']) && isset($LNG[$def['desc']]) ? $LNG[$def['desc']] : '',
                'requirements' => $requirement,
                'progress'    => pretty_number($metric),
                'earned'      => pretty_number(Achievements::earnedFor($def, $level)),
                'nextAntimatter' => pretty_number(Achievements::reward($def['am'], $nextLevel)),
                'nextPoints'  => pretty_number(Achievements::reward($def['ap'], $nextLevel)),
                'icon'        => $icon,
            );
        }

        $this->assign(array(
            'groupList'          => $groupList,
            'achievementList'    => $achievementList,
            'achievementPoints'  => pretty_number($totalEarned),
            'antimatter'         => pretty_number($USER['antimatter']),
            'selectedGroup'      => $group,
            'selectedGroupTitle' => $LNG[$groups[$group]],
        ));

        $this->display('page.achievements.default.tpl');
    }
}
