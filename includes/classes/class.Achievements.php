<?php

/**
 * Niburuspace-style achievements and Senate governators.
 *
 * Achievement definitions and reward curves are reconstructed from the live
 * captures in the private ops repo:
 *   - neoxnova/testdata/achievements.json (47 catalog entries)
 *   - neoxnova-ops/tools/explorer/data/messages*.json (reward anchors)
 *   - HumanAIShare screenshots (higher-level requirement anchors)
 *
 * Governator prices/effects are copied from the live page=gubernators JS:
 *   var GOVERNORS = {"dm_attack":{"MaxLevel":65,"priceAP":[50,1.07],...}}
 * captured in archive-02.har.
 */
class Achievements
{
    const MAX_LEVEL = 5000;

    private static $catalog = NULL;
    private static $levels = array();

    private static $groups = array(
        'all'   => 'ach_group_all',
        'daily' => 'ach_group_daily',
        'build' => 'ach_group_build',
        'tech'  => 'ach_group_tech',
        'fleet' => 'ach_group_fleet',
        'def'   => 'ach_group_def',
        'varia' => 'ach_group_varia',
    );

    // Live governor ids and the exact captured price/effect metadata.
    private static $governators = array(
        701 => array('max' => 65,  'ap' => array(50, 1.07),   'dm' => array(40000, 1.1),   'bonus' => array('Attack', 'Defensive')),
        702 => array('max' => 65,  'ap' => array(50, 1.075),  'dm' => array(40000, 1.1),   'bonus' => array('Shield')),
        703 => array('max' => 50,  'ap' => array(20, 1.1),    'dm' => array(7500, 1.08),   'bonus' => array('BuildTime')),
        704 => array('max' => 250, 'ap' => array(100, 1.01),  'dm' => array(30000, 1.022), 'bonus' => array('Resource')),
        705 => array('max' => 100, 'ap' => array(50, 1.022),  'dm' => array(10000, 1.055), 'bonus' => array('Energy')),
        706 => array('max' => 40,  'ap' => array(100, 1.12),  'dm' => array(25000, 1.14),  'bonus' => array('ResearchTime')),
        707 => array('max' => 20,  'ap' => array(100, 1.13),  'dm' => array(50000, 1.15),  'bonus' => array('FlyTime')),
    );

    private static $sourceNames = array(
        71 => 'Light conveyor', 72 => 'Average conveyor', 73 => 'Heavy conveyor',
        221 => 'Battleship class ONill', 222 => 'Flying death', 225 => 'Galleon',
        226 => 'Destroyer', 227 => 'Frigate', 228 => 'Black Wanderer',
        412 => 'Lepton Gun', 413 => 'Proton Gun', 414 => 'Canyon',
        415 => 'Quantum Gun', 416 => 'Hydrogen Gun', 417 => 'Dora Gun',
        419 => 'Particle Emitter',
    );

    public static function getGroups()
    {
        return self::$groups;
    }

    public static function getGovernators()
    {
        return self::$governators;
    }

    public static function getCatalog()
    {
        if (self::$catalog !== NULL) {
            return self::$catalog;
        }

        $c = array();

        // General
        $c['peaceful'] = self::def('all', 'Peaceful', array('counter' => 'peace_level'), array('linear', 5), array(65.329, 1.0425), array(12.729, 1.0425), 0, 'ach_desc_peaceful');
        $c['veteran'] = self::def('all', 'Veteran', array('counter' => 'battle_level'), array('linear', 5), array(125, 1.1204), array(13, 1.1204), 0, 'ach_desc_veteran');

        // Daily (daily reset is not wired yet; counters stay zero except wons)
        $c['raider'] = self::def('daily', 'Raider', array('counter' => 'wons'), array('linear', 15), array(200, 1.1), array(50, 1.1), 0, 'ach_desc_raider');
        $c['victory_hostal_day'] = self::def('daily', 'Victory Hostal / twenty-four hours', array('zero' => 'hostal_wins'), array('linear', 20), array(200, 1.1), array(50, 1.1), 0, 'ach_desc_victory_hostal');
        $c['successful_expo_day'] = self::def('daily', 'Successful expo / twenty-four hours', array('zero' => 'daily_expo'), array('linear', 20), array(250, 1.1), array(50, 1.1), 0, 'ach_desc_daily_expo');

        // Buildings
        $c['metal_miner'] = self::def('build', 'Metal Miner', array('building' => array(1)), array('linear', 3), array(9.905, 1.3508), array(0.987, 1.3508), 1);
        $c['crystal_miner'] = self::def('build', 'Crystal Miner', array('building' => array(2)), array('linear', 3), array(17.637, 1.3424), array(1.636, 1.3424), 2);
        $c['deuterium_miner'] = self::def('build', 'Deuterium Miner', array('building' => array(3)), array('linear', 3), array(15.599, 1.3961), array(1.545, 1.3961), 3);
        $c['small_producer'] = self::def('build', 'Small Producer', array('building' => array(71)), array('linear', 1), array(27.79, 1.2008), array(2.756, 1.2008), 71);
        $c['medium_producer'] = self::def('build', 'Medium Producer', array('building' => array(72)), array('linear', 1), array(31.083, 1.2196), array(3.121, 1.2196), 72);
        $c['large_producer'] = self::def('build', 'Large Producer', array('building' => array(73)), array('linear', 1), array(33.659, 1.2514), array(3.315, 1.2514), 73);
        $c['mad_scientist'] = self::def('build', 'Mad Scientist', array('building' => array(6)), array('linear', 2), array(90.274, 1.5482), array(9.1, 1.5482), 6);
        $c['moon_coloniser'] = self::def('build', 'Moon Coloniser', array('building' => array(41)), array('linear', 2), array(55, 1.389), array(6, 1.389), 41);
        $c['phalanx_sensor'] = self::def('build', 'Phalanx Sensor', array('building' => array(42)), array('linear', 2), array(80, 1.4), array(8, 1.4), 42);
        $c['terraformer'] = self::def('build', 'Terraformer', array('building' => array(33)), array('linear', 2), array(88.393, 1.2628), array(7.518, 1.2628), 33);

        // Research
        $c['spy'] = self::def('tech', 'Spy', array('tech' => array(106)), array('linear', 3), array(40.01, 1.3998), array(20.042, 1.3998), 106);
        $c['hacker'] = self::def('tech', 'Hacker', array('tech' => array(108)), array('linear', 3), array(40.113, 1.3996), array(20.063, 1.3996), 108);
        $c['unbeatable_tech'] = self::def('tech', 'Unbeatable technology', array('tech' => array(109, 111, 110)), array('linear', 2), array(50.068, 1.2999), array(25.032, 1.2999), 109);
        $c['expedition_members'] = self::def('tech', 'Expedition members', array('tech' => array(124)), array('linear', 4), array(64.86, 1.5998), array(30.002, 1.5998), 124);
        $c['gravitation'] = self::def('tech', 'Gravitation', array('tech' => array(199)), array('linear', 1), array(203.365, 1.0043), array(57.751, 1.0043), 199);
        $c['power_of_guns'] = self::def('tech', 'The power of guns', array('tech' => array(120, 121, 122)), array('linear', 3), array(49.971, 1.6002), array(24.974, 1.6002), 120);
        $c['energetic'] = self::def('tech', 'Energetic', array('tech' => array(113)), array('linear', 3), array(30.094, 1.4494), array(15.051, 1.4494), 113);
        $c['brotherhood'] = self::def('tech', 'Brotherhood', array('tech' => array(123)), array('linear', 2), array(20.121, 1.2999), array(10, 1.2999), 123);
        $c['increase_speed'] = self::def('tech', 'Increase the Speed', array('tech' => array(115, 117, 118)), array('linear', 2), array(40.047, 1.3997), array(20.054, 1.3997), 115);
        $c['geologist'] = self::def('tech', 'Geologist', array('tech' => array(131, 132, 133)), array('linear', 2), array(20.067, 1.2997), array(10.034, 1.2997), 131);

        // Fleet (unit requirements grow per achievement; factors fitted from the
        // HumanAIShare screenshots, see docs/HANDOFF.md)
        $c['fleet_fighters'] = self::def('fleet', 'Fleet Fighters', array('unit' => array(204, 205)), array('exp', 200000, 1.2509), array(150, 1.05), array(75, 1.05), 204);
        $c['fleet_support'] = self::def('fleet', 'Fleet Support', array('unit' => array(206, 207)), array('exp', 200000, 1.2269), array(200, 1.05), array(100, 1.05), 206);
        $c['battle_fleet'] = self::def('fleet', 'Battle fleet', array('unit' => array(213, 215)), array('exp', 200000, 1.2558), array(250, 1.055), array(125, 1.055), 213);
        $c['fleet_destruction'] = self::def('fleet', 'Fleet Destruction', array('unit' => array(216, 225)), array('exp', 5000, 1.3205), array(300, 1.07), array(150, 1.07), 216);
        $c['fleet_siege'] = self::def('fleet', 'Fleet Siege', array('unit' => array(211, 226)), array('exp', 100000, 1.472), array(200, 1.065), array(100, 1.065), 211);
        $c['heavy_fleet'] = self::def('fleet', 'Heavy fleet', array('unit' => array(227, 228)), array('exp', 2500, 1.435), array(350, 1.1), array(175, 1.1), 227);
        $c['imperial_fleet'] = self::def('fleet', 'Imperial fleet', array('unit' => array(218, 222, 221)), array('exp', 1000, 1.4482), array(400, 1.2), array(250, 1.2), 218);

        // Defense
        $c['light_defense'] = self::def('def', 'Light defense', array('unit' => array(401, 402)), array('exp', 200000, 1.2), array(150, 1.05), array(75, 1.05), 401);
        $c['simple_defense'] = self::def('def', 'Simple defense', array('unit' => array(403, 405)), array('exp', 150000, 1.2668), array(175, 1.055), array(100, 1.055), 403);
        $c['average_defense'] = self::def('def', 'Average defense', array('unit' => array(404, 406)), array('exp', 125000, 1.2466), array(175, 1.06), array(100, 1.06), 404);
        $c['high_defense'] = self::def('def', 'High defense', array('unit' => array(416, 417)), array('exp', 75000, 1.2715), array(250, 1.0645), array(125, 1.0645), 416);
        $c['heavy_defense'] = self::def('def', 'Heavy defense', array('unit' => array(410, 413)), array('exp', 15000, 1.4554), array(300, 1.1036), array(150, 1.1036), 410);
        $c['massive_defense'] = self::def('def', 'Massive defense', array('unit' => array(414, 415)), array('exp', 10000, 1.3902), array(350, 1.083), array(200, 1.083), 414);
        $c['imperial_defense'] = self::def('def', 'Imperial defense', array('unit' => array(413, 419, 412)), array('exp', 20000, 1.3696), array(350, 1.0953), array(200, 1.0953), 413);

        // Miscellaneous
        $c['fighter'] = self::def('varia', 'Fighter', array('counter' => 'wons'), array('linear', 50), array(150, 1.1), array(15, 1.1), 0);
        $c['moon_destroyer'] = self::def('varia', 'Moon destroyer', array('zero' => 'moons_destroyed'), array('linear', 3), array(200, 1.3), array(60, 1.3), 0);
        $c['moon_maker'] = self::def('varia', 'Mon maker', array('zero' => 'moons_created'), array('linear', 3), array(200, 1.3), array(50, 1.3), 0);
        $c['victory_hostal'] = self::def('varia', 'Victory Hostal', array('zero' => 'hostal_wins_total'), array('linear', 20), array(200, 1.3), array(40, 1.3), 0);
        $c['successful_expo'] = self::def('varia', 'Successfull expo', array('zero' => 'expo_total'), array('exp', 10, 1.3), array(200.269, 1.0998), array(50.219, 1.0998), 0);
        $c['dark_matter_finder'] = self::def('varia', 'Dark matter finder', array('zero' => 'dm_found'), array('exp', 50000, 1.1124), array(100, 1.0203), array(20, 1.0203), 0);
        $c['upgrade_finder'] = self::def('varia', 'Upgrade finder', array('zero' => 'upgrades_found'), array('exp', 3, 1.152), array(200, 1.0294), array(25, 1.0294), 0);
        $c['upgrade_engineer'] = self::def('varia', 'Upgrade engeneer', array('zero' => 'upgrades_activated'), array('exp', 10, 1.2), array(200, 1.03), array(50, 1.03), 0);

        foreach ($c as $key => &$def) {
            $def['key'] = $key;
        }
        unset($def);

        self::$catalog = $c;
        return self::$catalog;
    }

    private static function def($group, $name, $source, $req, $am, $ap, $icon, $desc = NULL)
    {
        return array(
            'key'    => $name,
            'group'  => $group,
            'name'   => $name,
            'source' => $source,
            'req'    => $req,
            'am'     => array((float)$am[0], (float)$am[1]),
            'ap'     => array((float)$ap[0], (float)$ap[1]),
            'icon'   => $icon,
            'desc'   => $desc,
        );
    }

    public static function getLevels($userId)
    {
        if (isset(self::$levels[$userId])) {
            return self::$levels[$userId];
        }

        $rows = Database::get()->select("SELECT achievement, level, progress FROM %%ACHIEVEMENTS%% WHERE userID = :userId;", array(
            ':userId' => $userId,
        ));

        $list = array();
        foreach ($rows as $row) {
            $list[$row['achievement']] = array(
                'level'    => (int)$row['level'],
                'progress' => (float)$row['progress'],
            );
        }

        self::$levels[$userId] = $list;
        return $list;
    }

    public static function collectStats($USER)
    {
        global $resource;

        $catalog = self::getCatalog();
        $columns = array();
        $resource_columns = array();

        foreach ($catalog as $def) {
            if (isset($def['source']['building'])) {
                foreach ($def['source']['building'] as $id) {
                    if (isset($resource[$id])) {
                        $resource_columns[$id] = $resource[$id];
                        $columns[$resource[$id]] = TRUE;
                    }
                }
            }
            if (isset($def['source']['unit'])) {
                foreach ($def['source']['unit'] as $id) {
                    if (isset($resource[$id])) {
                        $resource_columns[$id] = $resource[$id];
                        $columns[$resource[$id]] = TRUE;
                    }
                }
            }
        }

        $planetRows = array();
        if (!empty($columns)) {
            $select = array();
            foreach (array_keys($columns) as $column) {
                $select[] = '`' . $column . '`';
            }
            $sql = 'SELECT ' . implode(', ', $select) . ' FROM %%PLANETS%% WHERE id_owner = :userId AND destruyed = 0;';
            $planetRows = Database::get()->select($sql, array(':userId' => $USER['id']));
        }

        $stats = array();

        foreach ($catalog as $def) {
            if (isset($def['source']['building'])) {
                $values = array();
                foreach ($def['source']['building'] as $id) {
                    $value = 0;
                    if (isset($resource[$id])) {
                        foreach ($planetRows as $row) {
                            $value = max($value, (float)$row[$resource[$id]]);
                        }
                    }
                    $values[] = $value;
                }
                $stats['building_' . implode('_', $def['source']['building'])] = empty($values) ? 0 : min($values);
            }
            if (isset($def['source']['unit'])) {
                $values = array();
                foreach ($def['source']['unit'] as $id) {
                    $value = 0;
                    if (isset($resource[$id])) {
                        foreach ($planetRows as $row) {
                            $value += (float)$row[$resource[$id]];
                        }
                    }
                    $values[] = $value;
                }
                $stats['unit_' . implode('_', $def['source']['unit'])] = empty($values) ? 0 : min($values);
            }
            if (isset($def['source']['tech'])) {
                $values = array();
                foreach ($def['source']['tech'] as $id) {
                    $values[] = isset($USER[$resource[$id]]) ? (float)$USER[$resource[$id]] : 0;
                }
                $stats['tech_' . implode('_', $def['source']['tech'])] = empty($values) ? 0 : min($values);
            }
        }

        $stats['peace_level'] = isset($USER['peace_level']) ? (float)$USER['peace_level'] : 0;
        $stats['battle_level'] = isset($USER['battle_level']) ? (float)$USER['battle_level'] : 0;
        $stats['wons'] = isset($USER['wons']) ? (float)$USER['wons'] : 0;
        $stats['hostal_wins'] = 0;
        $stats['daily_expo'] = 0;
        $stats['moons_destroyed'] = 0;
        $stats['moons_created'] = 0;
        $stats['hostal_wins_total'] = 0;
        $stats['expo_total'] = 0;
        $stats['dm_found'] = 0;
        $stats['upgrades_found'] = isset($USER['arsenal_found']) ? (float)$USER['arsenal_found'] : 0;
        $stats['upgrades_activated'] = isset($USER['arsenal_activated']) ? (float)$USER['arsenal_activated'] : 0;

        return $stats;
    }

    public static function metricFor($def, $stats)
    {
        if (isset($def['source']['building'])) {
            $key = 'building_' . implode('_', $def['source']['building']);
            return isset($stats[$key]) ? $stats[$key] : 0;
        }
        if (isset($def['source']['unit'])) {
            $key = 'unit_' . implode('_', $def['source']['unit']);
            return isset($stats[$key]) ? $stats[$key] : 0;
        }
        if (isset($def['source']['tech'])) {
            $key = 'tech_' . implode('_', $def['source']['tech']);
            return isset($stats[$key]) ? $stats[$key] : 0;
        }
        if (isset($def['source']['counter'])) {
            return isset($stats[$def['source']['counter']]) ? $stats[$def['source']['counter']] : 0;
        }
        if (isset($def['source']['zero'])) {
            return isset($stats[$def['source']['zero']]) ? $stats[$def['source']['zero']] : 0;
        }
        return 0;
    }

    public static function levelFor($def, $metric)
    {
        if ($def['req'][0] === 'exp') {
            $base = $def['req'][1];
            $factor = $def['req'][2];
            if ($base <= 0 || $metric < $base) {
                return 0;
            }
            $level = 1 + (int)floor(log($metric / $base) / log($factor));
            return max(0, min(self::MAX_LEVEL, $level));
        }

        $step = max(1, (int)$def['req'][1]);
        return max(0, min(self::MAX_LEVEL, (int)floor($metric / $step)));
    }

    public static function nextRequirement($def, $level)
    {
        if ($def['req'][0] === 'exp') {
            return (float)($def['req'][1] * pow($def['req'][2], $level));
        }
        return (float)($def['req'][1] * ($level + 1));
    }

    public static function reward($curve, $level)
    {
        if ($level < 1) {
            return 0;
        }
        return (int)round($curve[0] * pow($curve[1], $level - 1));
    }

    public static function earnedFor($def, $level)
    {
        $total = 0;
        for ($i = 1; $i <= $level; $i++) {
            $total += self::reward($def['ap'], $i);
        }
        return $total;
    }

    /**
     * Re-evaluate all achievements for a user, award new levels and currencies.
     * Returns the awards made in this call (already persisted).
     */
    public static function evaluate(&$USER)
    {
        global $LNG;

        if (empty($USER['id'])) {
            return array();
        }

        $db = Database::get();
        $catalog = self::getCatalog();
        $stats = self::collectStats($USER);
        $levels = self::getLevels($USER['id']);

        $awards = array();
        $totalAP = 0;
        $totalAM = 0;

        foreach ($catalog as $key => $def) {
            $current = isset($levels[$key]['level']) ? (int)$levels[$key]['level'] : 0;
            $metric = self::metricFor($def, $stats);
            $reachable = self::levelFor($def, $metric);

            if ($reachable <= $current) {
                if (!isset($levels[$key]) || (float)$levels[$key]['progress'] !== (float)$metric) {
                    $db->insert("INSERT INTO %%ACHIEVEMENTS%% (userID, achievement, level, progress, updated_at)
                        VALUES (:userId, :key, :level, :progress, :time)
                        ON DUPLICATE KEY UPDATE progress = VALUES(progress), updated_at = VALUES(updated_at);", array(
                        ':userId' => $USER['id'],
                        ':key' => $key,
                        ':level' => $current,
                        ':progress' => $metric,
                        ':time' => TIMESTAMP,
                    ));
                    $levels[$key] = array('level' => $current, 'progress' => $metric);
                }
                continue;
            }

            $awardAM = 0;
            $awardAP = 0;
            for ($i = $current + 1; $i <= $reachable; $i++) {
                $awardAM += self::reward($def['am'], $i);
                $awardAP += self::reward($def['ap'], $i);
            }

            $db->insert("INSERT INTO %%ACHIEVEMENTS%% (userID, achievement, level, progress, updated_at)
                VALUES (:userId, :key, :level, :progress, :time)
                ON DUPLICATE KEY UPDATE level = VALUES(level), progress = VALUES(progress), updated_at = VALUES(updated_at);", array(
                ':userId' => $USER['id'],
                ':key' => $key,
                ':level' => $reachable,
                ':progress' => $metric,
                ':time' => TIMESTAMP,
            ));

            $levels[$key] = array('level' => $reachable, 'progress' => $metric);
            $totalAM += $awardAM;
            $totalAP += $awardAP;
            $awards[] = array(
                'key' => $key,
                'name' => $def['name'],
                'from' => $current + 1,
                'to' => $reachable,
                'am' => $awardAM,
                'ap' => $awardAP,
            );
        }

        if ($totalAM > 0 || $totalAP > 0) {
            $db->update("UPDATE %%USERS%% SET achievement_points = achievement_points + :ap, antimatter = antimatter + :am, academy_points = academy_points + :academyAp WHERE id = :userId;", array(
                ':ap' => $totalAP,
                ':academyAp' => $totalAP,
                ':am' => $totalAM,
                ':userId' => $USER['id'],
            ));
            $USER['achievement_points'] = (int)$USER['achievement_points'] + $totalAP;
            $USER['antimatter'] = (float)$USER['antimatter'] + $totalAM;
            $USER['academy_points'] = (isset($USER['academy_points']) ? (int)$USER['academy_points'] : 0) + $totalAP;

            foreach ($awards as $award) {
                $levelText = $award['from'] === $award['to'] ? (string)$award['to'] : $award['from'] . '-' . $award['to'];
                $textKey = $award['from'] === $award['to'] ? 'ach_msg_body' : 'ach_msg_body_multi';
                PlayerUtil::sendMessage($USER['id'], 0, $LNG['ach_msg_sender'], 4, $LNG['ach_msg_subject'],
                    sprintf($LNG[$textKey], $award['name'], $levelText, pretty_number($award['am']), pretty_number($award['ap'])), TIMESTAMP);
            }
        }

        self::$levels[$USER['id']] = $levels;
        return $awards;
    }

    public static function getGovernatorLevel($USER, $id)
    {
        $column = 'gov_level_' . (int)$id;
        return isset($USER[$column]) ? (int)$USER[$column] : 0;
    }

    public static function governatorAPCost($id, $level)
    {
        if (!isset(self::$governators[$id])) {
            return 0;
        }
        $price = self::$governators[$id]['ap'];
        return (int)round($price[0] * pow($price[1], max(0, $level)));
    }

    public static function governatorDMPrice($id, $level)
    {
        if (!isset(self::$governators[$id])) {
            return 0;
        }
        $price = self::$governators[$id]['dm'];
        return (int)round($price[0] * pow($price[1], max(0, $level)));
    }

    public static function governatorBonus($id, $level)
    {
        global $pricelist;

        $result = array();
        if (!isset(self::$governators[$id])) {
            return $result;
        }

        foreach (self::$governators[$id]['bonus'] as $bonusKey) {
            $base = isset($pricelist[$id]['bonus'][$bonusKey][0]) ? (float)$pricelist[$id]['bonus'][$bonusKey][0] : 0;
            $perLevel = 0.01;
            if ($base < 0) {
                $perLevel = -$perLevel;
            }
            $result[$bonusKey] = (int)floor(($base + $perLevel * $level) * 100);
        }

        return $result;
    }

    public static function sourceLabel($id)
    {
        global $LNG, $resource;

        if (isset($resource[$id]) && isset($LNG['tech'][$id])) {
            return $LNG['tech'][$id];
        }
        if (isset(self::$sourceNames[$id])) {
            return self::$sourceNames[$id];
        }
        return '#' . $id;
    }
}
