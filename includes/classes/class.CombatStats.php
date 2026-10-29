<?php

/**
 * Captured Niburuspace combat constants.
 *
 * Hull values are read from the live information cards
 * (neoxnova-ops/tools/explorer/data/unit-info.json and
 * neoxnova/testdata/combat.json). 2Moons derives hull from 10% of the build
 * cost, which does not reproduce the custom units, so OPBE is given the
 * explicit hull instead.
 */
class CombatStats
{
    private static $hull = array(
        202 => 400,
        203 => 1200,
        204 => 400,
        205 => 1100,
        206 => 2450,
        207 => 5800,
        208 => 45000,
        209 => 1600,
        210 => 750,
        211 => 11500,
        212 => 400,
        213 => 11000,
        214 => 950000,
        215 => 9000,
        216 => 1200000,
        217 => 5500,
        219 => 160000,
        220 => 13000000,
        221 => 52000000,
        222 => 33000000,
        225 => 160000,
        226 => 500000,
        227 => 4000000,
        228 => 12000000,
        401 => 200,
        402 => 200,
        403 => 800,
        404 => 3500,
        405 => 800,
        406 => 10000,
        407 => 200000,
        408 => 1000000,
        409 => 150000000,
        410 => 3000000,
        411 => 700000000,
        412 => 1500000,
        413 => 4300000,
        414 => 14000000,
        415 => 43000000,
        416 => 35000,
        417 => 50000,
        418 => 375000,
        419 => 7500000,
    );

    public static function hull($unitId)
    {
        $unitId = (int)$unitId;
        return isset(self::$hull[$unitId]) ? self::$hull[$unitId] : NULL;
    }

    /** Server-custom quadratic research curve: round(L*(L+2)/4). */
    public static function techBonusPct($level)
    {
        if (function_exists('opbeTechBonusPct')) {
            return opbeTechBonusPct($level);
        }
        $level = max(0, (float)$level);
        return (int)round($level * ($level + 2) / 4);
    }

    /** Additive percentage for one combat technology level. */
    public static function techPercent($level)
    {
        return self::techBonusPct($level);
    }
}
