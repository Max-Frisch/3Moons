<?php

/**
 * Niburuspace-style Academy skill tree.
 *
 * Catalog, prerequisites and point-cost curves are ported from the captured
 * live tree:
 *   - neoxnova/internal/game/academy.go (27 skills, 3 branches)
 *   - neoxnova-ops/tools/explorer/data/academy-map.json (level/cost anchors)
 *   - neoxnova-ops/tools/explorer/data/_academy.html / _academy2.html
 *
 * Branch reset tiers are from the captured reset panel:
 *   100% refund for 10000 Antimatter, 75% for 200000 DM, 50% free.
 *
 * Academy points are the live "Experience points" currency. For now they are
 * granted alongside Achievement Points (Reconstructed); the Online Bonus,
 * donation and peaceful-level sources arrive with P5 progression.
 */
class Academy
{
    const RESET_FULL = 100;
    const RESET_PARTIAL = 75;
    const RESET_FREE = 50;

    const RESET_ANTIMATTER_COST = 10000;
    const RESET_DARKMATTER_COST = 200000;

    private static $catalog = NULL;
    private static $levels = array();

    private static $branches = array(
        1 => 'acad_branch_attack',
        2 => 'acad_branch_economy',
        3 => 'acad_branch_defence',
    );

    public static function getBranches()
    {
        return self::$branches;
    }

    public static function getCatalog()
    {
        if (self::$catalog !== NULL) {
            return self::$catalog;
        }

        $c = array();

        // ---- 11xx Attack ----------------------------------------------------
        $c['1101'] = self::def('Weaponry', 1, array(), '+1% Attack', array('attack' => 1), 0, 1.1985);
        $c['1102'] = self::def('Weapons Class A', 1, array('1101' => 3), '+2% Attack, -1% Shields and armor', array('attack' => 2, 'shield' => -1, 'hull' => -1), 0, 1.369);
        $c['1104'] = self::def('Standardisation (Fleet)', 1, array('1101' => 5), '-1% fleet cost', array('fleet_cost' => 1), 0, 1.46195);
        $c['1105'] = self::def('Engine limitation', 1, array('1101' => 5), '+3% Fleet speed (all engine types)', array('fleet_speed' => 3), 2.897, 1.2527);
        $c['1106'] = self::def('Computerization', 1, array('1104' => 3), '+1% slots to the fleet', array('fleet_slot' => 1), 3, 0);
        $c['1103'] = self::def('Double attack', 1, array('1102' => 7), '+2% cumulative probability of shot shell', array(), 0, 1.542);
        $c['1107'] = self::def('Engine economication', 1, array('1105' => 5), '-2% deuterium consumption', array('fuel' => 2), 0, 1.615);
        $c['1108'] = self::def('Accurate shots', 1, array('1103' => 5), '+2% to the maximum number of destroys per round', array(), 0, 2.143);
        $c['1109'] = self::def('Chain reaction', 1, array('1103' => 7), '+1% chance of a chain reaction', array(), 0, 1.659);
        $c['1110'] = self::def('Strengthening explosion', 1, array('1103' => 7), '+8% destruction of additional units', array(), 0, 1.979);
        $c['1112'] = self::def('Relief ships', 1, array('1107' => 10), '-2% Fuel consumption, -2% Armour', array('fuel' => 2), 0, 0);
        $c['1111'] = self::def('Focusing', 1, array('1108' => 5), '+1% ship focus', array(), 0, 1.499);

        // ---- 12xx Economy ---------------------------------------------------
        $c['1201'] = self::def('Production speed', 2, array(), '+5% extraction of mines', array('mine' => 5), 0, 1.249);
        $c['1202'] = self::def('Energetics', 2, array('1201' => 3), '+2% to energy production', array('energy' => 2), 0, 1.369);
        $c['1203'] = self::def('Magistracy', 2, array('1202' => 7), '-2% studying time', array('research' => 2), 0, 1.634);
        $c['1204'] = self::def('Cosmo depot', 2, array('1202' => 7), '+5% more space in ress storage', array('storage' => 5), 0, 1.552);
        $c['1205'] = self::def('Academy of Sciences', 2, array('1203' => 12), '+1 to Intergalactic Research Network', array('irn' => 1), 0, 0);
        $c['1206'] = self::def('Expanding Empire', 2, array('1205' => 3), '+1 the maximum number of planets in the Empire', array('planets' => 1), 0, 0);
        $c['1207'] = self::def('Packing of cargo', 2, array('1204' => 5), '+5% more fleet space', array('cargo' => 5), 0, 1.341);
        $c['1208'] = self::def('Global defense', 2, array('1202' => 15), '+1 maximum number of shield domes', array(), 0, 0);
        $c['1209'] = self::def('Thermonuclear reaction', 2, array('1202' => 10), '+3% to power generation fusion reactor', array(), 0, 0);
        $c['1210'] = self::def('Solar panels', 2, array('1202' => 10), '+3% to develop solar power satellites', array(), 0, 0);
        $c['1211'] = self::def('Empire', 2, array('1206' => 5), '+1% attack, armor, shields and ship speed', array('attack' => 1, 'shield' => 1, 'hull' => 1, 'fleet_speed' => 1), 0, 0);

        // ---- 13xx Defence ---------------------------------------------------
        $c['1301'] = self::def('Defensive strategy', 3, array(), '+1% to shields and armor', array('shield' => 1, 'hull' => 1), 0, 1.199);
        $c['1304'] = self::def('Mechanics', 3, array('1301' => 8), '+1% Defense recover', array(), 2, 0);
        $c['1302'] = self::def('Defence class A', 3, array('1301' => 10), '+1% Shields and Armors, -2% Attack', array('shield' => 1, 'hull' => 1, 'attack' => -2), 0, 1.414);
        $c['1305'] = self::def('Shield complex', 3, array('1304' => 8), '+1% shields the defense', array(), 0, 0);
        $c['1306'] = self::def('Defense complex', 3, array('1304' => 8), '+1% armor in defense', array(), 0, 0);
        $c['1303'] = self::def('Double shields', 3, array('1302' => 4), '+2% the probability of double shields', array('double_shield' => 2), 0, 1.515);
        $c['1307'] = self::def('Seal Shield Dome', 3, array('1305' => 3), '+2% the probability of the missile destruction', array(), 0, 0);
        $c['1310'] = self::def('Standardisation (defense)', 3, array('1304' => 10), '-1% to the defense value', array(), 0, 0);
        $c['1308'] = self::def('Heavy Armour', 3, array('1303' => 7), '-2% destruction of rapid fire', array('rf_reduction' => 2), 0, 2.152);
        $c['1309'] = self::def('Maximum defense', 3, array('1306' => 20), '+1 the maximum number of orbital bases', array(), 0, 0);
        $c['1311'] = self::def('Sealing double shields', 3, array('1303' => 5), '+3% to density double shield', array(), 0, 1.491);

        foreach ($c as $code => &$skill) {
            $skill['code'] = $code;
            $skill['tier'] = self::tier($c, $code, 0);
        }
        unset($skill);

        self::$catalog = $c;
        return self::$catalog;
    }

    private static function def($name, $branch, $prereq, $effectText, $effect, $costBase, $costFactor)
    {
        return array(
            'name' => $name,
            'branch' => $branch,
            'prereq' => $prereq,
            'effect_text' => $effectText,
            'effect' => $effect,
            'cost_base' => $costBase > 0 ? $costBase : 1.0,
            'cost_factor' => $costFactor > 0 ? $costFactor : 1.25,
            'max_level' => 50,
            'tier' => 1,
        );
    }

    private static function tier($catalog, $code, $depth)
    {
        if ($depth > 8 || !isset($catalog[$code])) {
            return $depth;
        }
        $prereq = $catalog[$code]['prereq'];
        if (empty($prereq)) {
            return 1;
        }
        $parent = key($prereq);
        return self::tier($catalog, $parent, $depth + 1) + 1;
    }

    public static function getSkill($code)
    {
        $catalog = self::getCatalog();
        return isset($catalog[$code]) ? $catalog[$code] : NULL;
    }

    public static function cost($skill, $level)
    {
        if ($level < 1) {
            return 0;
        }
        return (int)round($skill['cost_base'] * pow($skill['cost_factor'], $level - 1));
    }

    public static function costRange($skill, $from, $to)
    {
        $to = min($to, $skill['max_level']);
        if ($to <= $from || $from < 0) {
            return 0;
        }
        $total = 0;
        for ($level = $from + 1; $level <= $to; $level++) {
            $total += self::cost($skill, $level);
        }
        return $total;
    }

    public static function affordableLevel($skill, $from, $to, $points)
    {
        $to = min($to, $skill['max_level']);
        $best = $from;
        $cost = 0;
        for ($level = $from + 1; $level <= $to; $level++) {
            $cost += self::cost($skill, $level);
            if ($cost > $points) {
                break;
            }
            $best = $level;
        }
        return $best;
    }

    public static function prereqMet($skill, $levels)
    {
        foreach ($skill['prereq'] as $code => $level) {
            if (!isset($levels[$code]) || $levels[$code] < $level) {
                return false;
            }
        }
        return true;
    }

    public static function getLevels($userId)
    {
        $userId = (int)$userId;
        if (isset(self::$levels[$userId])) {
            return self::$levels[$userId];
        }
        if ($userId <= 0) {
            return array();
        }

        $rows = Database::get()->select("SELECT skill, level FROM %%ACADEMY%% WHERE userID = :userId AND level > 0;", array(
            ':userId' => $userId,
        ));

        $list = array();
        foreach ($rows as $row) {
            $list[$row['skill']] = (int)$row['level'];
        }

        self::$levels[$userId] = $list;
        return $list;
    }

    public static function getPoints($USER)
    {
        return isset($USER['academy_points']) ? (int)$USER['academy_points'] : 0;
    }

    public static function upgrade(&$USER, $code, $target)
    {
        $skill = self::getSkill($code);
        if ($skill === NULL) {
            return array('ok' => false, 'reason' => 'unknown');
        }

        $target = max(1, min((int)$target, $skill['max_level']));
        $levels = self::getLevels($USER['id']);
        $current = isset($levels[$code]) ? (int)$levels[$code] : 0;

        if (!self::prereqMet($skill, $levels)) {
            return array('ok' => false, 'reason' => 'prereq');
        }
        if ($target <= $current) {
            return array('ok' => false, 'reason' => 'level');
        }

        $points = self::getPoints($USER);
        $newLevel = self::affordableLevel($skill, $current, $target, $points);
        if ($newLevel <= $current) {
            return array('ok' => false, 'reason' => 'points');
        }

        $cost = self::costRange($skill, $current, $newLevel);
        $db = Database::get();
        $handle = $db->getHandle();

        try {
            $handle->beginTransaction();
            $db->update("UPDATE %%USERS%% SET
                academy_points = academy_points - :cost,
                academy_points_spent = academy_points_spent + :cost
                WHERE id = :userId AND academy_points >= :cost;", array(
                ':cost' => $cost,
                ':userId' => $USER['id'],
            ));
            $db->insert("INSERT INTO %%ACADEMY%% (userID, skill, level)
                VALUES (:userId, :skill, :level)
                ON DUPLICATE KEY UPDATE level = VALUES(level);", array(
                ':userId' => $USER['id'],
                ':skill' => $code,
                ':level' => $newLevel,
            ));
            $handle->commit();
        } catch (Exception $e) {
            if ($handle->inTransaction()) {
                $handle->rollBack();
            }
            throw $e;
        }

        $levels[$code] = $newLevel;
        self::$levels[(int)$USER['id']] = $levels;
        $USER['academy_points'] = $points - $cost;
        $USER['academy_points_spent'] = (isset($USER['academy_points_spent']) ? (int)$USER['academy_points_spent'] : 0) + $cost;

        return array(
            'ok' => true,
            'level' => $newLevel,
            'requested' => $target,
            'spent' => $cost,
            'partial' => $newLevel < $target,
        );
    }

    public static function resetBranch(&$USER, $branch, $percent)
    {
        $branch = (int)$branch;
        $percent = (int)$percent;
        if (!isset(self::$branches[$branch])) {
            return array('ok' => false, 'reason' => 'unknown');
        }
        if (!in_array($percent, array(self::RESET_FULL, self::RESET_PARTIAL, self::RESET_FREE), true)) {
            return array('ok' => false, 'reason' => 'unknown');
        }

        $catalog = self::getCatalog();
        $levels = self::getLevels($USER['id']);
        $spent = 0;
        $codes = array();
        foreach ($levels as $code => $level) {
            if (!isset($catalog[$code]) || $catalog[$code]['branch'] !== $branch || $level <= 0) {
                continue;
            }
            $spent += self::costRange($catalog[$code], 0, $level);
            $codes[] = $code;
        }

        if (empty($codes)) {
            return array('ok' => false, 'reason' => 'empty');
        }

        $antimatterCost = $percent === self::RESET_FULL ? self::RESET_ANTIMATTER_COST : 0;
        $darkMatterCost = $percent === self::RESET_PARTIAL ? self::RESET_DARKMATTER_COST : 0;

        if ($antimatterCost > 0 && (float)$USER['antimatter'] < $antimatterCost) {
            return array('ok' => false, 'reason' => 'antimatter');
        }
        if ($darkMatterCost > 0 && (float)$USER['darkmatter'] < $darkMatterCost) {
            return array('ok' => false, 'reason' => 'darkmatter');
        }

        $refund = (int)round($spent * $percent / 100.0);
        $db = Database::get();
        $handle = $db->getHandle();

        try {
            $handle->beginTransaction();
            $db->update("UPDATE %%USERS%% SET
                antimatter = antimatter - :antimatter,
                darkmatter = darkmatter - :darkmatter,
                academy_points = academy_points + :refund
                WHERE id = :userId;", array(
                ':antimatter' => $antimatterCost,
                ':darkmatter' => $darkMatterCost,
                ':refund' => $refund,
                ':userId' => $USER['id'],
            ));
            foreach ($codes as $code) {
                $db->delete("DELETE FROM %%ACADEMY%% WHERE userID = :userId AND skill = :skill;", array(
                    ':userId' => $USER['id'],
                    ':skill' => $code,
                ));
            }
            $handle->commit();
        } catch (Exception $e) {
            if ($handle->inTransaction()) {
                $handle->rollBack();
            }
            throw $e;
        }

        foreach ($codes as $code) {
            unset($levels[$code]);
        }
        self::$levels[(int)$USER['id']] = $levels;
        $USER['academy_points'] = self::getPoints($USER) + $refund;
        $USER['antimatter'] = (float)$USER['antimatter'] - $antimatterCost;
        $USER['darkmatter'] = (float)$USER['darkmatter'] - $darkMatterCost;

        return array(
            'ok' => true,
            'refunded' => $refund,
            'spent' => $spent,
            'percent' => $percent,
        );
    }

    private static function sum($levels, $key)
    {
        $total = 0;
        foreach (self::getCatalog() as $code => $skill) {
            if (!empty($levels[$code]) && isset($skill['effect'][$key])) {
                $total += $levels[$code] * $skill['effect'][$key];
            }
        }
        return $total;
    }

    public static function attackPct($levels)
    {
        return self::sum($levels, 'attack');
    }

    public static function shieldPct($levels)
    {
        return self::sum($levels, 'shield');
    }

    public static function hullPct($levels)
    {
        return self::sum($levels, 'hull');
    }

    public static function minePct($levels)
    {
        return self::sum($levels, 'mine');
    }

    public static function energyPct($levels)
    {
        return self::sum($levels, 'energy');
    }

    public static function researchPct($levels)
    {
        return self::sum($levels, 'research');
    }

    public static function storagePct($levels)
    {
        return self::sum($levels, 'storage');
    }

    public static function cargoPct($levels)
    {
        return self::sum($levels, 'cargo');
    }

    public static function fleetCostPct($levels)
    {
        return self::sum($levels, 'fleet_cost');
    }

    public static function fleetSpeedPct($levels)
    {
        return self::sum($levels, 'fleet_speed');
    }

    public static function fuelPct($levels)
    {
        return self::sum($levels, 'fuel');
    }

    public static function fleetSlots($levels)
    {
        return self::sum($levels, 'fleet_slot');
    }

    public static function irn($levels)
    {
        return self::sum($levels, 'irn');
    }

    public static function planets($levels)
    {
        return self::sum($levels, 'planets');
    }

    public static function doubleShieldPct($levels)
    {
        return self::sum($levels, 'double_shield');
    }

    public static function rfReductionPct($levels)
    {
        return self::sum($levels, 'rf_reduction');
    }

    public static function levelsFor($USER)
    {
        if (isset($USER['academy_levels']) && is_array($USER['academy_levels'])) {
            return $USER['academy_levels'];
        }
        return self::getLevels(isset($USER['id']) ? $USER['id'] : 0);
    }

    public static function applyFactors($USER, $factor)
    {
        $levels = self::levelsFor($USER);
        if (empty($levels)) {
            return $factor;
        }

        $factor['Attack'] += self::attackPct($levels) / 100;
        $factor['Shield'] += self::shieldPct($levels) / 100;
        $factor['Defensive'] += self::hullPct($levels) / 100;
        $factor['Resource'] += self::minePct($levels) / 100;
        $factor['Energy'] += self::energyPct($levels) / 100;
        $factor['ResearchTime'] -= self::researchPct($levels) / 100;
        $factor['ResourceStorage'] += self::storagePct($levels) / 100;
        $factor['ShipStorage'] += self::cargoPct($levels) / 100;
        $factor['FlyTime'] += self::fleetSpeedPct($levels) / 100;
        $factor['FleetSlots'] += self::fleetSlots($levels);
        $factor['Planets'] += self::planets($levels);

        return $factor;
    }

    public static function fleetCostReductionPct($USER)
    {
        return self::fleetCostPct(self::levelsFor($USER));
    }

    public static function fuelReductionPct($USER)
    {
        return self::fuelPct(self::levelsFor($USER));
    }

    public static function irnLevels($USER)
    {
        return self::irn(self::levelsFor($USER));
    }
}
