<?php

/**
 * Niburuspace-style progression: peaceful level, combat XP/level, Online Bonus
 * and timed premium bonuses.
 *
 * The peaceful online-time curve, step bonuses and Bonus-button payout are
 * ported from neoxnova/internal/game/peaceful.go, which was calibrated against
 * the live "Bonus Button Peaceful" captures (L3/4/5/72/76/77). The battle-level
 * threshold curve is fitted from the live level tooltips:
 *   10,700 at level 0, 11,449 at level 1, 31,588 at 16, 149,745 at 39,
 *   257,289 at 47, 579,464 at 59, 620,027 at 60, 663,429 at 61.
 * That is exactly round(10700 * 1.07^level).
 *
 * Combat XP is linear in the defender's destroyed resource value. The captured
 * battle reports split into a base group (defender loss / ~635,000,000) and a
 * 2.5x event group (/ ~255,000,000); the base divisor is used here.
 *
 * The premium catalog, costs and growth factors are copied verbatim from the
 * live page=premium pblist and premium.js cost formula.
 */
class Progression
{
    const ONLINE_MIN_GAP = 15;
    const ONLINE_CAP = 300;

    const BONUS_INTERVAL = 1200;
    const BONUS_PROGRESS_PCT = 10.0;
    const BONUS_DM_BASE = 7000;
    const BONUS_DM_SLOPE = 630;

    const PEACEFUL_QUAD_A = 37.0;
    const PEACEFUL_QUAD_B = -57.3;
    const PEACEFUL_QUAD_C = 4076.0;

    const STEP_FLEET_SLOTS = 10;
    const STEP_FIELDS_PLANET = 5;
    const STEP_FIELDS_MOON = 12;
    const STEP_SMALL_FACTORY = 10;
    const STEP_MEDIUM_FACTORY = 25;
    const STEP_LARGE_FACTORY = 50;

    const BATTLE_BASE_XP = 10700.0;
    const BATTLE_GROWTH = 1.07;
    const BATTLE_MAX_LEVEL = 5000;

    const COMBAT_XP_DIVISOR = 635000000.0;

    const PREMIUM_MAX_DAYS = 365;

    private static $premiumCache = array();
    private static $premiumCatalog = NULL;


    public static function getPremiumCatalog()
    {
        if (self::$premiumCatalog !== NULL) {
            return self::$premiumCatalog;
        }

        $c = array();

        $c['prem_res'] = self::prem('pr_prem_res', 'pr_prem_res_desc', 'ext', 1000, 29, 1.07, 90, 0, 200, '%');
        $c['prem_storage'] = self::prem('pr_prem_storage', 'pr_prem_storage_desc', 'buy', 0, 1, 1.05, 4, 0, 1000, '%');
        $c['prem_s_build'] = self::prem('pr_prem_s_build', 'pr_prem_s_build_desc', 'ext', 100, 24, 1.9, 50, 0, 100, '%');
        $c['prem_o_build'] = self::prem('pr_prem_o_build', 'pr_prem_o_build_desc', 'ext', 5, 100, 1.3, 1, 0, 100, '+');
        $c['prem_button'] = self::prem('pr_prem_button', 'pr_prem_button_desc', 'buy', 0, 525, 1.45, 1, 2, 10, 'x');
        $c['prem_speed_button'] = self::prem('pr_prem_speed_button', 'pr_prem_speed_button_desc', 'buy', 0, 22, 1.1, 10, 0, 1000, '%');
        $c['prem_expedition'] = self::prem('pr_prem_expedition', 'pr_prem_expedition_desc', 'buy', 0, 30, 1.02, 10, 0, 1000, '%');
        $c['prem_count_expiditeon'] = self::prem('pr_prem_count_expiditeon', 'pr_prem_count_expiditeon_desc', 'buy', 0, 500, 1.3, 1, 0, 100, '+');
        $c['prem_speed_expiditeon'] = self::prem('pr_prem_speed_expiditeon', 'pr_prem_speed_expiditeon_desc', 'buy', 0, 50, 1.02, 8, 0, 1000, '%');
        $c['prem_moon_dextruct'] = self::prem('pr_prem_moon_dextruct', 'pr_prem_moon_dextruct_desc', 'buy', 0, 8500, 2.0, 2, 2, 10, 'x');
        $c['prem_leveling'] = self::prem('pr_prem_leveling', 'pr_prem_leveling_desc', 'ext', 100, 32, 1.08, 25, 0, 1000, '%');
        $c['prem_batle_leveling'] = self::prem('pr_prem_batle_leveling', 'pr_prem_batle_leveling_desc', 'buy', 0, 37, 1.08, 25, 0, 1000, '%');
        $c['prem_bank_ally'] = self::prem('pr_prem_bank_ally', 'pr_prem_bank_ally_desc', 'buy', 0, 200, 1.2, 1, 2, 100, 'x');
        $c['prem_conveyors_l'] = self::prem('pr_prem_conveyors_l', 'pr_prem_conveyors_l_desc', 'buy', 0, 35, 1.02, 10, 0, 1000, '%');
        $c['prem_conveyors_s'] = self::prem('pr_prem_conveyors_s', 'pr_prem_conveyors_s_desc', 'buy', 0, 45, 1.02, 10, 0, 1000, '%');
        $c['prem_conveyors_t'] = self::prem('pr_prem_conveyors_t', 'pr_prem_conveyors_t_desc', 'buy', 0, 55, 1.02, 10, 0, 1000, '%');
        $c['prem_prod_from_colly'] = self::prem('pr_prem_prod_from_colly', 'pr_prem_prod_from_colly_desc', 'buy', 0, 110, 1.13, 15, 0, 1000, '%');
        $c['prem_moon_creat'] = self::prem('pr_prem_moon_creat', 'pr_prem_moon_creat_desc', 'buy', 0, 100, 1.04, 2, 0, 100, '%');
        $c['prem_fuel_consumption'] = self::prem('pr_prem_fuel_consumption', 'pr_prem_fuel_consumption_desc', 'buy', 0, 55, 1.12, 3, 0, 1000, '%');

        self::$premiumCatalog = $c;
        return self::$premiumCatalog;
    }

    private static function prem($name, $desc, $kind, $fixed, $cost, $factor, $step, $min, $max, $unit)
    {
        return array(
            'name' => $name,
            'desc' => $desc,
            'kind' => $kind,
            'fixed' => $fixed,
            'cost' => (float)$cost,
            'factor' => (float)$factor,
            'step' => max(1, (float)$step),
            'min' => (int)$min,
            'max' => (int)$max,
            'unit' => $unit,
        );
    }

    public static function premiumCost($item, $count, $days)
    {
        $catalog = self::getPremiumCatalog();
        if (!isset($catalog[$item])) {
            return 0;
        }
        $def = $catalog[$item];
        $count = max(1, (int)$count);
        $days = max(0, (int)$days);

        $costPerDay = $def['cost'] * pow($def['factor'], floor($count / $def['step']) - $def['min']) * $count;
        $discount = 1 - min(0.50, $days * 0.5 / 100);
        return (int)round($days * $costPerDay * $discount);
    }

    public static function premiumActive($USER)
    {
        $userId = is_array($USER) ? (int)(isset($USER['id']) ? $USER['id'] : 0) : (int)$USER;
        if ($userId <= 0) {
            return array();
        }
        if (isset(self::$premiumCache[$userId])) {
            return self::$premiumCache[$userId];
        }

        $rows = Database::get()->select(
            "SELECT item, value, expires_at FROM %%PREMIUM%% WHERE userID = :userId AND expires_at > :now;",
            array(':userId' => $userId, ':now' => TIMESTAMP)
        );

        $list = array();
        foreach ($rows as $row) {
            $list[$row['item']] = array(
                'value' => (float)$row['value'],
                'expires_at' => (int)$row['expires_at'],
            );
        }

        self::$premiumCache[$userId] = $list;
        return $list;
    }

    public static function premiumValue($USER, $item)
    {
        $active = self::premiumActive($USER);
        return isset($active[$item]) ? (float)$active[$item]['value'] : 0.0;
    }

    public static function premiumValueForUser($userId, $item)
    {
        return self::premiumValue((int)$userId, $item);
    }

    public static function premiumQueueSlots($USER)
    {
        return (int)self::premiumValue($USER, 'prem_o_build');
    }

    public static function premiumFuelReductionPct($USER)
    {
        return self::premiumValue($USER, 'prem_fuel_consumption');
    }

    public static function premiumCombatXpPct($USER)
    {
        return self::premiumValue($USER, 'prem_batle_leveling');
    }

    public static function premiumExperiencePct($USER)
    {
        return self::premiumValue($USER, 'prem_leveling');
    }

    public static function premiumBonusMultiplier($USER)
    {
        $value = self::premiumValue($USER, 'prem_button');
        return $value >= 1 ? $value : 1.0;
    }

    public static function premiumBonusCooldown($USER)
    {
        $pct = self::premiumValue($USER, 'prem_speed_button');
        $seconds = (int)round(self::BONUS_INTERVAL / (1 + $pct / 100));
        return max(1, $seconds);
    }

    public static function buyPremium(&$USER, $item, $count, $days)
    {
        $catalog = self::getPremiumCatalog();
        if (!isset($catalog[$item])) {
            return array('ok' => false, 'reason' => 'item');
        }

        $def = $catalog[$item];
        $days = (int)$days;
        if ($days < 1 || $days > self::PREMIUM_MAX_DAYS) {
            return array('ok' => false, 'reason' => 'days');
        }

        if ($def['kind'] === 'ext') {
            $count = (int)$def['fixed'];
        } else {
            $count = max($def['min'], min($def['max'], (int)$count));
        }

        $cost = self::premiumCost($item, $count, $days);
        $userId = (int)$USER['id'];
        $antimatter = (float)$USER['antimatter'];
        if ($cost > $antimatter) {
            return array('ok' => false, 'reason' => 'antimatter');
        }

        $db = Database::get();
        $handle = $db->getHandle();
        try {
            $handle->beginTransaction();
            $row = $db->selectSingle(
                "SELECT value, expires_at FROM %%PREMIUM%% WHERE userID = :userId AND item = :item FOR UPDATE;",
                array(':userId' => $userId, ':item' => $item)
            );
            $base = TIMESTAMP;
            $value = (float)$count;
            if (!empty($row)) {
                $base = max($base, (int)$row['expires_at']);
                if ($def['kind'] === 'buy') {
                    $value = max((float)$row['value'], (float)$count);
                } else {
                    $value = (float)$def['fixed'];
                }
            }
            $expires = $base + $days * 86400;

            $db->insert(
                "INSERT INTO %%PREMIUM%% (userID, item, value, expires_at)
                 VALUES (:userId, :item, :value, :expires)
                 ON DUPLICATE KEY UPDATE value = VALUES(value), expires_at = VALUES(expires_at);",
                array(
                    ':userId' => $userId,
                    ':item' => $item,
                    ':value' => $value,
                    ':expires' => $expires,
                )
            );
            $db->update(
                "UPDATE %%USERS%% SET antimatter = antimatter - :cost WHERE id = :userId;",
                array(':cost' => $cost, ':userId' => $userId)
            );
            $handle->commit();
        } catch (Exception $e) {
            if ($handle->inTransaction()) {
                $handle->rollBack();
            }
            return array('ok' => false, 'reason' => 'database');
        }

        $USER['antimatter'] = $antimatter - $cost;
        unset(self::$premiumCache[$userId]);

        return array(
            'ok' => true,
            'cost' => $cost,
            'value' => $value,
            'expires' => $expires,
            'days' => $days,
        );
    }

    public static function grantStartingPremium($userId)
    {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return;
        }

        $items = array(
            'prem_res' => 1000,
            'prem_s_build' => 100,
            'prem_o_build' => 5,
            'prem_leveling' => 100,
        );
        $expires = TIMESTAMP + 86400;
        $db = Database::get();
        foreach ($items as $item => $value) {
            $db->insert(
                "INSERT INTO %%PREMIUM%% (userID, item, value, expires_at)
                 VALUES (:userId, :item, :value, :expires)
                 ON DUPLICATE KEY UPDATE value = VALUES(value), expires_at = VALUES(expires_at);",
                array(
                    ':userId' => $userId,
                    ':item' => $item,
                    ':value' => $value,
                    ':expires' => $expires,
                )
            );
        }
        unset(self::$premiumCache[$userId]);
    }

    public static function peacefulLevelSecondsRaw($level)
    {
        $level = max(1, (int)$level);
        return self::PEACEFUL_QUAD_A * $level * $level
            + self::PEACEFUL_QUAD_B * $level
            + self::PEACEFUL_QUAD_C;
    }

    public static function cumulativePeacefulSeconds($level)
    {
        $level = max(0, (int)$level);
        $n = (float)$level;
        $s1 = $n * ($n + 1) / 2;
        $s2 = $n * ($n + 1) * (2 * $n + 1) / 6;
        return self::PEACEFUL_QUAD_A * $s2
            + self::PEACEFUL_QUAD_B * $s1
            + self::PEACEFUL_QUAD_C * $n;
    }

    public static function peacefulState($seconds)
    {
        $seconds = max(0.0, (float)$seconds);
        if ($seconds <= 0) {
            return array(
                'level' => 0,
                'progress' => 0.0,
                'next_seconds' => (int)self::peacefulLevelSecondsRaw(1),
            );
        }

        $lo = 0;
        $hi = 1;
        while (self::cumulativePeacefulSeconds($hi) <= $seconds) {
            $hi *= 2;
            if ($hi > 100000) {
                break;
            }
        }
        while ($lo < $hi) {
            $mid = (int)(($lo + $hi + 1) / 2);
            if (self::cumulativePeacefulSeconds($mid) <= $seconds) {
                $lo = $mid;
            } else {
                $hi = $mid - 1;
            }
        }

        $level = $lo;
        $done = $seconds - self::cumulativePeacefulSeconds($level);
        $duration = self::peacefulLevelSecondsRaw($level + 1);
        $progress = $duration > 0 ? $done / $duration * 100.0 : 0.0;
        $progress = max(0.0, min(100.0, $progress));

        return array(
            'level' => $level,
            'progress' => $progress,
            'next_seconds' => (int)max(0, $duration - $done),
        );
    }

    public static function peacefulBonuses($level)
    {
        $level = max(0, (int)$level);
        return array(
            'mines' => $level,
            'studies' => $level,
            'energy' => $level,
            'fleet_slots' => (int)floor($level / self::STEP_FLEET_SLOTS),
            'fields_planet' => (int)floor($level / self::STEP_FIELDS_PLANET),
            'fields_moon' => (int)floor($level / self::STEP_FIELDS_MOON),
            'small_factory' => (int)floor($level / self::STEP_SMALL_FACTORY),
            'medium_factory' => (int)floor($level / self::STEP_MEDIUM_FACTORY),
            'large_factory' => (int)floor($level / self::STEP_LARGE_FACTORY),
        );
    }

    public static function academyPointsBase($level)
    {
        $level = max(0, (int)$level);
        return 1.0 + (8.5 - 1.0) * ($level - 3) / 74.0;
    }

    public static function bonusRewardForLevel($level, $buttonMultiplier = 1.0, $experiencePct = 0.0)
    {
        $level = max(0, (int)$level);
        $buttonMultiplier = max(1.0, (float)$buttonMultiplier);
        $experiencePct = max(0.0, (float)$experiencePct);

        return array(
            'darkmatter' => (int)round((self::BONUS_DM_BASE + self::BONUS_DM_SLOPE * $level) * $buttonMultiplier),
            'peaceful_seconds' => (int)round(self::peacefulLevelSecondsRaw($level + 1) * self::BONUS_PROGRESS_PCT / 100.0 * (1 + $experiencePct / 100.0)),
            'experience_pct' => self::BONUS_PROGRESS_PCT * (1 + $experiencePct / 100.0),
            'academy_points' => (int)round(self::academyPointsBase($level) * $buttonMultiplier),
        );
    }

    public static function bonusReward($USER)
    {
        $state = self::peacefulState((float)(isset($USER['peaceful_seconds']) ? $USER['peaceful_seconds'] : 0));
        return self::bonusRewardForLevel(
            $state['level'],
            self::premiumBonusMultiplier($USER),
            self::premiumExperiencePct($USER)
        );
    }

    public static function bonusState($USER)
    {
        $state = self::peacefulState((float)(isset($USER['peaceful_seconds']) ? $USER['peaceful_seconds'] : 0));
        $readyAt = (int)(isset($USER['bonus_button_ready_at']) ? $USER['bonus_button_ready_at'] : 0);
        $state['ready'] = $readyAt <= TIMESTAMP;
        $state['ready_seconds'] = max(0, $readyAt - TIMESTAMP);
        $state['reward'] = self::bonusReward($USER);
        $state['bonuses'] = self::peacefulBonuses($state['level']);
        return $state;
    }

    public static function claimBonus(&$USER)
    {
        $userId = (int)$USER['id'];
        $db = Database::get();
        $handle = $db->getHandle();

        try {
            $handle->beginTransaction();
            $row = $db->selectSingle(
                "SELECT peaceful_seconds, bonus_button_ready_at FROM %%USERS%% WHERE id = :userId FOR UPDATE;",
                array(':userId' => $userId)
            );
            if (empty($row)) {
                $handle->rollBack();
                return array('ok' => false, 'reason' => 'user');
            }

            $readyAt = (int)$row['bonus_button_ready_at'];
            if ($readyAt > TIMESTAMP) {
                $handle->rollBack();
                return array('ok' => false, 'reason' => 'cooldown', 'ready_seconds' => $readyAt - TIMESTAMP);
            }

            $state = self::peacefulState((float)$row['peaceful_seconds']);
            $reward = self::bonusRewardForLevel(
                $state['level'],
                self::premiumBonusMultiplier($USER),
                self::premiumExperiencePct($USER)
            );
            $next = TIMESTAMP + self::premiumBonusCooldown($USER);

            $db->update(
                "UPDATE %%USERS%% SET
                    darkmatter = darkmatter + :dm,
                    peaceful_seconds = peaceful_seconds + :seconds,
                    academy_points = academy_points + :points,
                    bonus_button_ready_at = :ready,
                    bonus_last_click_at = :now
                 WHERE id = :userId;",
                array(
                    ':dm' => $reward['darkmatter'],
                    ':seconds' => $reward['peaceful_seconds'],
                    ':points' => $reward['academy_points'],
                    ':ready' => $next,
                    ':now' => TIMESTAMP,
                    ':userId' => $userId,
                )
            );
            $handle->commit();
        } catch (Exception $e) {
            if ($handle->inTransaction()) {
                $handle->rollBack();
            }
            return array('ok' => false, 'reason' => 'database');
        }

        $USER['darkmatter'] = (float)$USER['darkmatter'] + $reward['darkmatter'];
        $USER['peaceful_seconds'] = (int)$USER['peaceful_seconds'] + $reward['peaceful_seconds'];
        $USER['academy_points'] = (int)$USER['academy_points'] + $reward['academy_points'];
        $USER['bonus_button_ready_at'] = $next;
        $USER['bonus_last_click_at'] = TIMESTAMP;

        return array(
            'ok' => true,
            'reward' => $reward,
            'next' => $next,
        );
    }

    public static function accrueOnlineTime(&$USER)
    {
        $userId = (int)(isset($USER['id']) ? $USER['id'] : 0);
        if ($userId <= 0) {
            return;
        }

        $seen = (int)(isset($USER['peaceful_seen_at']) ? $USER['peaceful_seen_at'] : 0);
        if ($seen <= 0) {
            Database::get()->update(
                "UPDATE %%USERS%% SET peaceful_seen_at = :now WHERE id = :userId;",
                array(':now' => TIMESTAMP, ':userId' => $userId)
            );
            $USER['peaceful_seen_at'] = TIMESTAMP;
            return;
        }

        $gap = TIMESTAMP - $seen;
        if ($gap < self::ONLINE_MIN_GAP) {
            return;
        }
        $gap = min($gap, self::ONLINE_CAP);
        $earned = (int)round($gap * (1 + self::premiumExperiencePct($USER) / 100.0));

        Database::get()->update(
            "UPDATE %%USERS%% SET peaceful_seconds = peaceful_seconds + :earned, peaceful_seen_at = :now WHERE id = :userId;",
            array(':earned' => $earned, ':now' => TIMESTAMP, ':userId' => $userId)
        );

        $USER['peaceful_seconds'] = (int)(isset($USER['peaceful_seconds']) ? $USER['peaceful_seconds'] : 0) + $earned;
        $USER['peaceful_seen_at'] = TIMESTAMP;
    }

    public static function battleThreshold($level)
    {
        $level = max(0, (int)$level);
        return (int)round(self::BATTLE_BASE_XP * pow(self::BATTLE_GROWTH, $level));
    }

    public static function battleState($xp)
    {
        $remaining = max(0, (int)$xp);
        $level = 0;
        while ($level < self::BATTLE_MAX_LEVEL) {
            $need = self::battleThreshold($level);
            if ($remaining < $need) {
                break;
            }
            $remaining -= $need;
            $level++;
        }
        $need = self::battleThreshold($level);

        return array(
            'level' => $level,
            'progress' => $need > 0 ? $remaining / $need * 100.0 : 0.0,
            'xp' => $remaining,
            'next_xp' => $need,
        );
    }

    public static function attachLevels(&$USER)
    {
        $peaceful = self::peacefulState((float)(isset($USER['peaceful_seconds']) ? $USER['peaceful_seconds'] : 0));
        $USER['peace_level'] = $peaceful['level'];
        $USER['peaceful_level'] = $peaceful['level'];
        $USER['peaceful_progress'] = $peaceful['progress'];
        $USER['peaceful_next_seconds'] = $peaceful['next_seconds'];

        $battle = self::battleState((int)(isset($USER['combat_xp']) ? $USER['combat_xp'] : 0));
        $USER['battle_level'] = $battle['level'];
        $USER['battle_progress'] = $battle['progress'];
        $USER['battle_xp'] = $battle['xp'];
        $USER['battle_next_xp'] = $battle['next_xp'];

        self::premiumActive($USER);
    }

    public static function fieldsBonus($USER, $planetType = 1)
    {
        $level = isset($USER['peace_level']) ? (int)$USER['peace_level'] : 0;
        if ($level <= 0) {
            $level = self::peacefulState((float)(isset($USER['peaceful_seconds']) ? $USER['peaceful_seconds'] : 0))['level'];
        }
        return (int)floor($level / ((int)$planetType === 3 ? self::STEP_FIELDS_MOON : self::STEP_FIELDS_PLANET));
    }

    public static function applyFactors($USER, $factor)
    {
        $level = isset($USER['peace_level']) ? (int)$USER['peace_level'] : 0;
        if ($level > 0) {
            $factor['Resource'] += $level / 100.0;
            $factor['Energy'] += $level / 100.0;
            $factor['ResearchTime'] -= $level / 100.0;
            $factor['FleetSlots'] += (int)floor($level / self::STEP_FLEET_SLOTS);
        }

        $active = self::premiumActive($USER);
        if (!empty($active)) {
            $res = isset($active['prem_res']) ? (float)$active['prem_res']['value'] : 0.0;
            if ($res > 0) {
                $factor['Resource'] += $res / 100.0;
            }

            $storage = isset($active['prem_storage']) ? (float)$active['prem_storage']['value'] : 0.0;
            if ($storage > 0) {
                $factor['ResourceStorage'] += $storage / 100.0;
            }

            $buildSpeed = isset($active['prem_s_build']) ? (float)$active['prem_s_build']['value'] : 0.0;
            if ($buildSpeed > 0) {
                $timeReduction = 1 - 1 / (1 + $buildSpeed / 100.0);
                $factor['BuildTime'] -= $timeReduction;
                $factor['ResearchTime'] -= $timeReduction;
            }
        }

        return $factor;
    }

    public static function lostResources($lostUnits)
    {
        $total = 0.0;
        if (!is_array($lostUnits)) {
            return is_numeric($lostUnits) ? (float)$lostUnits : 0.0;
        }
        foreach ($lostUnits as $value) {
            if (is_array($value)) {
                $total += self::lostResources($value);
            } elseif (is_numeric($value)) {
                $total += (float)$value;
            }
        }
        return $total;
    }

    public static function combatXpForLoss($lossValue, $experiencePct = 0.0)
    {
        $lossValue = max(0.0, (float)$lossValue);
        if ($lossValue <= 0) {
            return 0;
        }
        $xp = $lossValue / self::COMBAT_XP_DIVISOR * (1 + max(0.0, (float)$experiencePct) / 100.0);
        return max(0, (int)round($xp));
    }

    public static function awardCombatXP($userId, $lossValue)
    {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return 0;
        }
        $xp = self::combatXpForLoss($lossValue, self::premiumValueForUser($userId, 'prem_batle_leveling'));
        if ($xp <= 0) {
            return 0;
        }

        Database::get()->update(
            "UPDATE %%USERS%% SET combat_xp = combat_xp + :xp WHERE id = :userId;",
            array(':xp' => $xp, ':userId' => $userId)
        );
        return $xp;
    }
}
