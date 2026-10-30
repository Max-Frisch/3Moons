<?php

/**
 * Pure helpers for the Niburuspace expedition model.
 *
 * The live outcome mix, pirate/alien split, mirrored enemy and dark-matter
 * scaling are ported from neoxnova/internal/game/expedition.go (calibrated
 * against the 2026-10 private ops captures). The mission class supplies random
 * floats in [0,1), so these helpers stay deterministic and testable.
 */
class Expedition
{
    const OUTCOME_RESOURCES      = 'resources';
    const OUTCOME_SHIPS          = 'ships';
    const OUTCOME_COMBAT         = 'combat';
    const OUTCOME_NOTHING        = 'nothing';
    const OUTCOME_DARKMATTER     = 'darkmatter';
    const OUTCOME_DELAY          = 'delay';
    const OUTCOME_FAST           = 'fast';
    const OUTCOME_BLACKHOLE      = 'blackhole';
    const OUTCOME_BLACKHOLE_LOOT = 'blackhole-loot';
    const OUTCOME_STARDUST       = 'stardust';

    const NPC_PIRATES = 'pirates';
    const NPC_ALIENS  = 'aliens';

    /** X4 2026-10-15 outcome distribution (sums to 1.0). */
    public static function outcomeWeights()
    {
        return array(
            self::OUTCOME_RESOURCES      => 0.1707,
            self::OUTCOME_SHIPS          => 0.2905,
            self::OUTCOME_COMBAT         => 0.1350,
            self::OUTCOME_NOTHING        => 0.0707,
            self::OUTCOME_DARKMATTER     => 0.1368,
            self::OUTCOME_DELAY          => 0.1047,
            self::OUTCOME_FAST           => 0.0388,
            self::OUTCOME_BLACKHOLE_LOOT => 0.0170,
            self::OUTCOME_BLACKHOLE      => 0.0168,
            self::OUTCOME_STARDUST       => 0.0190,
        );
    }

    public static function rollOutcome($roll)
    {
        $weights = self::outcomeWeights();
        $total   = array_sum($weights);
        $point   = max(0.0, min(0.999999999, (float)$roll)) * $total;
        foreach ($weights as $outcome => $weight) {
            if ($point < $weight) {
                return $outcome;
            }
            $point -= $weight;
        }
        return self::OUTCOME_STARDUST;
    }

    /** Random float in [0,1), from the same generator the mission uses. */
    public static function rand()
    {
        return mt_rand() / mt_getrandmax();
    }

    /**
     * Cosmetic flavour catalogue: the live sys_expe_* language keys. Each
     * outcome has its own pool; the caller passes a [0,1) roll so the choice
     * stays deterministic in tests. Ported from the captured language files.
     */
    public static function flavourKeys($outcome)
    {
        switch ($outcome) {
            case self::OUTCOME_RESOURCES:
                return array('1_1', '1_2', '1_3', '1_4', '2_1', '2_2', '2_3', '3_1', '3_2');
            case self::OUTCOME_SHIPS:
                return array('1_1', '1_2', '1_3', '1_4', '2_1', '2_2', '3_1', '3_2');
            case self::OUTCOME_DARKMATTER:
                return array('1_1', '1_2', '1_3', '1_4', '1_5', '2_1', '2_2', '2_3', '3_1', '3_2');
            case self::OUTCOME_STARDUST:
                return array('1', '2', '3', '4');
            case self::OUTCOME_NOTHING:
                return array('1', '2', '3', '4', '5', '6', '7', '8', '9');
            case self::OUTCOME_DELAY:
                return array('1', '2', '3', '4', '5', '6');
            case self::OUTCOME_FAST:
                return array('1', '2', '3');
            case self::OUTCOME_BLACKHOLE:
                return array('1', '2', '3', '4');
            default:
                return array();
        }
    }

    /** Pick a flavour key from the outcome's pool for a [0,1) roll. */
    public static function flavour($outcome, $roll)
    {
        $keys = self::flavourKeys($outcome);
        if (empty($keys)) {
            return '';
        }
        $roll = max(0.0, min(0.999999999, (float)$roll));
        return $keys[(int)floor($roll * count($keys))];
    }

    /** Combat split: 58 pirates : 11 aliens = 84% / 16%. */
    public static function rollNPC($roll)
    {
        return ((float)$roll < 0.84) ? self::NPC_PIRATES : self::NPC_ALIENS;
    }

    /** Minimum enemy template on top of the mirrored fleet. */
    public static function template()
    {
        return array(
            203 => array(20, 180, FALSE),
            204 => array(5, 90, FALSE),
            206 => array(5, 85, FALSE),
            207 => array(4, 90, FALSE),
            213 => array(5, 45, FALSE),
            215 => array(0, 20, TRUE),
            216 => array(0, 3, TRUE),
        );
    }

    /** Strongest general research bonus, mirrored onto expedition NPCs. */
    public static function mirroredResearchBonus($weapons, $shield, $armour)
    {
        return max(
            CombatStats::techBonusPct($weapons),
            CombatStats::techBonusPct($shield),
            CombatStats::techBonusPct($armour)
        );
    }

    /**
     * Build the NPC fleet. Pirates mirror 60-69% and roll weaker; aliens mirror
     * 85-94% and roll at/above the player's general research.
     */
    public static function enemyComposition(array $fleet, $npc, $mirrorRoll, $strengthRoll, $mirroredBonus)
    {
        if ($npc === self::NPC_ALIENS) {
            $mirror = 0.85 + ((float)$mirrorRoll * 0.09);
            $roll   = 0.70 + 1.80 * pow(max(0.0, min(1.0, (float)$strengthRoll)), 2);
        } else {
            $mirror = 0.60 + ((float)$mirrorRoll * 0.09);
            $roll   = 0.10 + 1.70 * pow(max(0.0, min(1.0, (float)$strengthRoll)), 1.5);
        }

        $enemy = array();
        foreach ($fleet as $code => $amount) {
            $count = (int)round($mirror * (float)$amount);
            if ($count > 0) {
                $enemy[$code] = $count;
            }
        }

        foreach (self::template() as $code => $spec) {
            list($min, $max, $alienOnly) = $spec;
            if ($alienOnly && $npc !== self::NPC_ALIENS) {
                continue;
            }
            $count = $min;
            if ($max > $min) {
                $count += mt_rand(0, $max - $min);
            }
            if ($count > 0) {
                $enemy[$code] = (isset($enemy[$code]) ? $enemy[$code] : 0) + $count;
            }
        }

        return array($enemy, $mirroredBonus * $roll);
    }

    /** Resource find: 30-100% of cargo, metal-heavy 55/35/10. */
    public static function loot($capacity, $roll)
    {
        $capacity = max(0, (int)$capacity);
        if ($capacity <= 0) {
            return array(901 => 0, 902 => 0, 903 => 0);
        }
        $total   = (int)floor($capacity * (0.30 + ((float)$roll * 0.70)));
        $metal   = (int)floor($total * 0.55);
        $crystal = (int)floor($total * 0.35);
        $deut    = max(0, $total - $metal - $crystal);
        return array(901 => $metal, 902 => $crystal, 903 => $deut);
    }

    /** Rare positive black hole: a normal find multiplied by 1.5-3x. */
    public static function blackHoleLoot($capacity, $lootRoll, $boostRoll)
    {
        $base = self::loot($capacity, $lootRoll);
        $sum  = array_sum($base);
        if ($sum <= 0 || $capacity <= 0) {
            return array(901 => 0, 902 => 0, 903 => 0);
        }
        $total = (int)round($sum * (1.5 + ((float)$boostRoll * 1.5)));
        $total = min($total, (int)$capacity);
        $metal   = (int)floor($total * 0.55);
        $crystal = (int)floor($total * 0.35);
        $deut    = max(0, $total - $metal - $crystal);
        return array(901 => $metal, 902 => $crystal, 903 => $deut);
    }

    /** Dark matter scales with the sent fleet's metal+crystal points. */
    public static function darkMatter($points, $roll)
    {
        $points = max(0, (int)$points);
        if ($points <= 0) {
            return 100 + mt_rand(0, 4900);
        }
        $perPoint = 3.3e-9 + ((float)$roll * (1.5e-8 - 3.3e-9));
        return max(100, (int)round($points * $perPoint));
    }

    /**
     * Recovered ships: 5-35% of fleet points across 1-3 random types.
     * $rolls is consumed in order; extra values are ignored.
     */
    public static function recovery($fleetPoints, array $rolls)
    {
        $fleetPoints = max(0, (int)$fleetPoints);
        if ($fleetPoints <= 0) {
            return array();
        }
        $rolls = array_values($rolls);
        $take  = function ($index) use (&$rolls) {
            return isset($rolls[$index]) ? max(0.0, min(0.999999, (float)$rolls[$index])) : 0.5;
        };

        $budget = (int)round($fleetPoints * (0.05 + $take(0) * 0.30));
        if ($budget <= 0) {
            return array();
        }

        $types = array(202, 203, 204, 205, 206, 207, 213, 215, 217, 219, 225);
        $count = 1 + (int)floor($take(1) * 3);
        $found = array();
        $left  = $budget;
        for ($i = 0; $i < $count && $left > 0; $i++) {
            $code = $types[(int)floor($take(2 + $i) * count($types)) % count($types)];
            $cost = self::unitCost($code);
            if ($cost <= 0) {
                continue;
            }
            $share = $left;
            if ($i < $count - 1) {
                $share = (int)floor($left / 2) + (int)floor($take(5 + $i) * ($left / 2 + 1));
            }
            $amount = max(1, (int)floor($share / $cost));
            $found[$code] = (isset($found[$code]) ? $found[$code] : 0) + $amount;
            $left -= $amount * $cost;
        }
        return $found;
    }

    private static function unitCost($code)
    {
        if (!isset($GLOBALS['pricelist'][$code])) {
            return 0;
        }
        $price = $GLOBALS['pricelist'][$code]['cost'];
        return (int)$price[901] + (int)$price[902];
    }

    /** Metal+crystal value of a fleet, the expedition scale input. */
    public static function fleetPoints(array $fleet)
    {
        $total = 0;
        foreach ($fleet as $code => $amount) {
            $total += self::unitCost($code) * (int)$amount;
        }
        return $total;
    }
}
