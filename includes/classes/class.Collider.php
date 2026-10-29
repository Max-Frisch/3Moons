<?php

/**
 * Moon Collider (live page=kollaider, building sprite 69).
 *
 * Moon-only structure bought with Antimatter. It produces Dark Matter per hour
 * independent of the moon's size/fields. Captured levels (page at level L shows
 * level L+1):
 *
 *   0 -> 4 752 DM/h, 850 ATM
 *   1 -> 10 454 DM/h, 1 148 ATM
 *   2 -> 17 250 DM/h, 1 549 ATM
 *
 * The cost curve is clean (x1.35 per level). The production curve is only
 * anchored at three levels; the table is authoritative for those and a
 * provisional quadratic extrapolates past them.
 */
class Collider
{
    const ID = 69;
    const MAX_LEVEL = 255;

    const BASE_ANTIMATTER_COST = 850.0;
    const COST_FACTOR = 1.35;

    const COMBAT_PCT_PER_LEVEL = 3.0;
    const PROD_QUAD_A = 547.0;
    const PROD_QUAD_B = 4061.0;
    const PROD_QUAD_C = 144.0;

    private static $productionTable = array(4752, 10454, 17250);

    public static function cost($targetLevel)
    {
        $targetLevel = max(1, (int)$targetLevel);
        return (int)round(self::BASE_ANTIMATTER_COST * pow(self::COST_FACTOR, $targetLevel - 1));
    }

    public static function production($level)
    {
        $level = (int)$level;
        if ($level <= 0) {
            return 0;
        }
        if ($level <= count(self::$productionTable)) {
            return self::$productionTable[$level - 1];
        }

        $l = (float)$level;
        return (int)round(self::PROD_QUAD_A * $l * $l + self::PROD_QUAD_B * $l + self::PROD_QUAD_C);
    }

    /**
     * Combined production multiplier: combat level (+3%/level) and the premium
     * "Generation collider" percentage.
     */
    public static function factor($battleLevel, $premiumPct)
    {
        $combat = 1.0 + self::COMBAT_PCT_PER_LEVEL * max(0, (int)$battleLevel) / 100.0;
        $premium = 1.0 + max(0.0, (float)$premiumPct) / 100.0;

        return $combat * $premium;
    }

    public static function productionFor($level, $battleLevel = 0, $premiumPct = 0.0)
    {
        if ((int)$level <= 0) {
            return 0;
        }
        return (int)round(self::production($level) * self::factor($battleLevel, $premiumPct));
    }

    public static function productionForUser($level, $USER)
    {
        $battleLevel = self::battleLevel($USER);
        $premiumPct = Progression::premiumValue($USER, 'prem_prod_from_colly');
        return self::productionFor($level, $battleLevel, $premiumPct);
    }

    private static function battleLevel($USER)
    {
        $xp = is_array($USER) && isset($USER['combat_xp']) ? (int)$USER['combat_xp'] : 0;
        $state = Progression::battleState($xp);
        return (int)$state['level'];
    }

    /**
     * Accrue account-wide Dark Matter produced by every owned Collider since
     * the previous request. The timestamp is always advanced, so building a
     * Collider later never pays out retroactively.
     */
    public static function accrue(&$USER)
    {
        $userId = is_array($USER) && isset($USER['id']) ? (int)$USER['id'] : 0;
        if ($userId <= 0) {
            return;
        }

        $last = isset($USER['collider_dm_at']) ? (int)$USER['collider_dm_at'] : 0;
        $now = defined('TIMESTAMP') ? TIMESTAMP : time();

        if ($last <= 0) {
            $USER['collider_dm_at'] = $now;
            return;
        }
        if ($now <= $last) {
            return;
        }
        if (!empty($USER['urlaubs_modus'])) {
            $USER['collider_dm_at'] = $now;
            return;
        }

        $rows = Database::get()->select(
            "SELECT kollaider FROM %%PLANETS%% WHERE id_owner = :userId AND destruyed = 0 AND planet_type = '3' AND kollaider > 0;",
            array(':userId' => $userId)
        );

        $total = 0;
        foreach ($rows as $row) {
            $total += self::productionForUser((int)$row['kollaider'], $USER);
        }

        $gain = $total * ($now - $last) / 3600.0;
        $db = Database::get();

        // Conditional on the old timestamp so parallel requests cannot both
        // pay out the same interval.
        $db->update(
            "UPDATE %%USERS%% SET darkmatter = darkmatter + :gain, collider_dm_at = :now
             WHERE id = :userId AND collider_dm_at = :last AND collider_dm_at > 0;",
            array(
                ':gain' => $gain,
                ':now' => $now,
                ':userId' => $userId,
                ':last' => $last,
            )
        );

        $row = $db->selectSingle(
            "SELECT darkmatter, collider_dm_at FROM %%USERS%% WHERE id = :userId;",
            array(':userId' => $userId)
        );
        if (!empty($row)) {
            $USER['darkmatter'] = (float)$row['darkmatter'];
            $USER['collider_dm_at'] = (int)$row['collider_dm_at'];
        }
    }
}
