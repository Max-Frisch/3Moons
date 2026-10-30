<?php

/**
 * Niburuspace black market math: merchant refund, Dark-Matter build/tech
 * prices, Planetarium field expansion and planet relocation.
 *
 * Curves are ported from neoxnova/internal/game/{blackmarket,fields,relocation}.go
 * which were captured from the live pages/captures:
 *   - Merchant of Fleets: 50% of a unit's base cost.
 *   - Buildings/Technology market: ceil((M+C+D) / 1000) Dark Matter per level.
 *   - Fields: cumulative +1..+6 = 200/420/662/928/1221/1543 DM (200 * 1.1^n).
 *   - Relocation: 15000 per galaxy, 1000 per system, 2500 per position.
 */
class BlackMarket
{
    const DM_MARKET_DIVISOR = 1000;

    const FIELD_BASE = 200.0;
    const FIELD_FACTOR = 1.1;

    const RELOCATION_GALAXY = 15000;
    const RELOCATION_SYSTEM = 1000;
    const RELOCATION_POSITION = 2500;

    const RELOCATION_COOLDOWN = 3600;
    const ATTACK_LOCK_TIME = 900;

    /**
     * Planetarium "increase in diameter" sink, captured from the live page:
     * one Stardust plus the metal/crystal debris field at the celestial's
     * coordinates buys +276..414 km and +12..18 fields.
     */
    const DIAMETER_METAL_DEBRIS = 100000000000;
    const DIAMETER_CRYSTAL_DEBRIS = 50000000000;
    const DIAMETER_STARDUST = 1;
    const DIAMETER_MIN_KM = 276;
    const DIAMETER_MAX_KM = 414;
    const DIAMETER_MIN_FIELDS = 12;
    const DIAMETER_MAX_FIELDS = 18;

    public static function merchantRate($configured)
    {
        return max(0, min(100, (int)$configured));
    }

    /**
     * Resources refunded by the Merchant of Fleets for a batch with the given
     * base resource cost. Rounded down to whole units, like the live page.
     */
    public static function merchantRefund($cost, $ratePercent)
    {
        $rate = self::merchantRate($ratePercent);
        $refund = array();
        if ($rate <= 0 || !is_array($cost)) {
            return $refund;
        }

        foreach (array(901, 902, 903, 921) as $resourceId) {
            if (empty($cost[$resourceId])) {
                continue;
            }
            $refund[$resourceId] = (int)floor($cost[$resourceId] * $rate / 100);
        }

        return $refund;
    }

    /**
     * Dark Matter price of one instant level in the buildings/technology
     * market: one DM per divisor resource points of the level's cost.
     */
    public static function dmMarketPrice($cost, $divisor = self::DM_MARKET_DIVISOR)
    {
        if (!is_array($cost)) {
            return 0;
        }

        $divisor = (int)$divisor;
        if ($divisor <= 0) {
            $divisor = 1;
        }

        $total = 0.0;
        foreach (array(901, 902, 903) as $resourceId) {
            if (isset($cost[$resourceId])) {
                $total += (float)$cost[$resourceId];
            }
        }

        if ($total <= 0) {
            return 0;
        }

        return (int)ceil($total / $divisor);
    }

    /**
     * Dark Matter cost to add fields to a celestial that already bought
     * `purchased` fields from the Planetarium.
     */
    public static function fieldExpansionCost($purchased, $additional)
    {
        $additional = (int)$additional;
        if ($additional <= 0) {
            return 0;
        }
        $purchased = max(0, (int)$purchased);

        $scale = self::FIELD_BASE / (self::FIELD_FACTOR - 1.0);
        $cost = $scale
            * pow(self::FIELD_FACTOR, $purchased)
            * (pow(self::FIELD_FACTOR, $additional) - 1.0);

        return (int)round($cost);
    }

    /**
     * Map two uniform [0,1) rolls onto the captured diameter/field gains.
     * Both bounds are inclusive, matching the live ranges.
     */
    public static function diameterGains($diameterRoll, $fieldsRoll)
    {
        return array(
            self::expansionRoll(self::DIAMETER_MIN_KM, self::DIAMETER_MAX_KM, $diameterRoll),
            self::expansionRoll(self::DIAMETER_MIN_FIELDS, self::DIAMETER_MAX_FIELDS, $fieldsRoll),
        );
    }

    private static function expansionRoll($min, $max, $roll)
    {
        $roll = (float)$roll;
        if ($roll < 0) {
            $roll = 0.0;
        }
        if ($roll >= 1) {
            $roll = 0.999999999;
        }
        return (int)($min + floor($roll * ($max - $min + 1)));
    }

    public static function relocationCost($fromGalaxy, $fromSystem, $fromPosition, $toGalaxy, $toSystem, $toPosition)
    {
        return self::RELOCATION_GALAXY * abs((int)$fromGalaxy - (int)$toGalaxy)
            + self::RELOCATION_SYSTEM * abs((int)$fromSystem - (int)$toSystem)
            + self::RELOCATION_POSITION * abs((int)$fromPosition - (int)$toPosition);
    }

    public static function validRelocationTarget($galaxy, $system, $position, $maxGalaxy, $maxSystem, $maxPlanets)
    {
        return $galaxy >= 1 && $galaxy <= (int)$maxGalaxy
            && $system >= 1 && $system <= (int)$maxSystem
            && $position >= 1 && $position <= (int)$maxPlanets;
    }

    public static function relocationLeavesSystem($fromGalaxy, $fromSystem, $toGalaxy, $toSystem)
    {
        return (int)$fromGalaxy !== (int)$toGalaxy || (int)$fromSystem !== (int)$toSystem;
    }

    public static function sameCoordinates($fromGalaxy, $fromSystem, $fromPosition, $toGalaxy, $toSystem, $toPosition)
    {
        return (int)$fromGalaxy === (int)$toGalaxy
            && (int)$fromSystem === (int)$toSystem
            && (int)$fromPosition === (int)$toPosition;
    }
}
