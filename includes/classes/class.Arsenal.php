<?php

/**
 * Niburuspace-style Arsenal upgrades and the upgrade market.
 *
 * Catalog, activation rules, fleet-point tiers and drop pools are ported from
 * the captured live/code reference:
 *   - neoxnova/internal/game/arsenal.go
 *   - neoxnova/internal/store/arsenal_store.go
 *   - neoxnova/testdata/map/arsenal.txt / market.txt
 *   - neoxnova/docs/ARSENAL_UPGRADES_IMPLEMENTATION.md
 *
 * Per-activation values are the live page=arsenal "next bonus" bracket values.
 * Only the three conveyor upgrades stay catalog-only until the conveyor
 * buildings (71-73) land with P7.
 */
class Arsenal
{
    const DROP_CHANCE = 0.10;
    const GUARANTEED_LEVEL = 10;
    const CHANCE_STEP = 0.02;
    const CHANCE_FLOOR = 0.75;
    const FAILURE_VALUE_DROP = 0.10;
    const LOT_HOURS = 72;
    const MAX_LOT_AMOUNT = 25;
    const MAX_LOT_RATE = 1000000;

    private static $catalog = NULL;
    private static $owned = array();

    // Live unit-card classes (neoxnova/testdata/unit_classes.json), reduced to
    // the primary weapon, armour, shield and engine class per unit. Unknown
    // codes simply receive no Arsenal bonus.
    private static $unitClasses = array(
        202 => array('weapon' => '',             'armor' => 'light',  'shield' => 'light',  'engine' => 'combustion'),
        203 => array('weapon' => '',             'armor' => 'light',  'shield' => 'light',  'engine' => 'combustion'),
        204 => array('weapon' => 'standard',     'armor' => 'light',  'shield' => 'light',  'engine' => 'combustion'),
        205 => array('weapon' => 'standard',     'armor' => 'light',  'shield' => 'light',  'engine' => 'impulse'),
        206 => array('weapon' => 'ion',          'armor' => 'medium', 'shield' => 'medium', 'engine' => 'impulse'),
        207 => array('weapon' => 'laser',        'armor' => 'medium', 'shield' => 'medium', 'engine' => 'hyperspace'),
        208 => array('weapon' => '',             'armor' => 'light',  'shield' => 'light',  'engine' => 'impulse'),
        209 => array('weapon' => '',             'armor' => 'light',  'shield' => 'light',  'engine' => 'combustion'),
        210 => array('weapon' => '',             'armor' => 'light',  'shield' => '',       'engine' => 'combustion'),
        211 => array('weapon' => 'plasma',       'armor' => 'medium', 'shield' => 'medium', 'engine' => 'impulse'),
        212 => array('weapon' => '',             'armor' => 'light',  'shield' => '',       'engine' => ''),
        213 => array('weapon' => 'laser',        'armor' => 'medium', 'shield' => 'medium', 'engine' => 'hyperspace'),
        214 => array('weapon' => 'gravitational','armor' => 'heavy',  'shield' => 'heavy',  'engine' => 'hyperspace'),
        215 => array('weapon' => 'laser',        'armor' => 'medium', 'shield' => 'medium', 'engine' => 'hyperspace'),
        216 => array('weapon' => 'laser',        'armor' => 'heavy',  'shield' => 'heavy',  'engine' => 'hyperspace'),
        217 => array('weapon' => 'standard',     'armor' => 'medium', 'shield' => 'medium', 'engine' => 'hyperspace'),
        218 => array('weapon' => 'laser',        'armor' => 'heavy',  'shield' => 'heavy',  'engine' => 'hyperspace'),
        219 => array('weapon' => '',             'armor' => 'medium', 'shield' => 'medium', 'engine' => 'hyperspace'),
        220 => array('weapon' => '',             'armor' => 'medium', 'shield' => 'medium', 'engine' => 'hyperspace'),
        221 => array('weapon' => 'plasma',       'armor' => 'heavy',  'shield' => 'heavy',  'engine' => 'hyperspace'),
        222 => array('weapon' => 'laser',        'armor' => 'heavy',  'shield' => 'heavy',  'engine' => 'hyperspace'),
        223 => array('weapon' => '',             'armor' => 'light',  'shield' => 'light',  'engine' => 'combustion'),
        224 => array('weapon' => 'laser',        'armor' => 'medium', 'shield' => 'medium', 'engine' => 'hyperspace'),
        225 => array('weapon' => 'laser',        'armor' => 'heavy',  'shield' => 'heavy',  'engine' => 'hyperspace'),
        226 => array('weapon' => 'laser',        'armor' => 'heavy',  'shield' => 'heavy',  'engine' => 'hyperspace'),
        227 => array('weapon' => 'laser',        'armor' => 'heavy',  'shield' => 'heavy',  'engine' => 'hyperspace'),
        228 => array('weapon' => 'laser',        'armor' => 'heavy',  'shield' => 'heavy',  'engine' => 'hyperspace'),
        401 => array('weapon' => 'standard',     'armor' => 'light',  'shield' => 'light',  'engine' => ''),
        402 => array('weapon' => 'laser',        'armor' => 'light',  'shield' => 'light',  'engine' => ''),
        403 => array('weapon' => 'laser',        'armor' => 'medium', 'shield' => 'light',  'engine' => ''),
        404 => array('weapon' => 'standard',     'armor' => 'medium', 'shield' => 'medium', 'engine' => ''),
        405 => array('weapon' => 'ion',          'armor' => 'medium', 'shield' => 'medium', 'engine' => ''),
        406 => array('weapon' => 'plasma',       'armor' => 'medium', 'shield' => 'medium', 'engine' => ''),
        407 => array('weapon' => '',             'armor' => 'light',  'shield' => 'heavy',  'engine' => ''),
        408 => array('weapon' => '',             'armor' => 'medium', 'shield' => 'heavy',  'engine' => ''),
        409 => array('weapon' => '',             'armor' => 'heavy',  'shield' => 'heavy',  'engine' => ''),
        410 => array('weapon' => 'gravitational','armor' => 'heavy',  'shield' => 'heavy',  'engine' => ''),
        411 => array('weapon' => 'laser',        'armor' => 'heavy',  'shield' => 'heavy',  'engine' => ''),
        412 => array('weapon' => 'laser',        'armor' => 'heavy',  'shield' => 'heavy',  'engine' => ''),
        413 => array('weapon' => 'plasma',       'armor' => 'heavy',  'shield' => 'heavy',  'engine' => ''),
        414 => array('weapon' => 'ion',          'armor' => 'heavy',  'shield' => 'heavy',  'engine' => ''),
        415 => array('weapon' => 'ion',          'armor' => 'heavy',  'shield' => 'heavy',  'engine' => ''),
        416 => array('weapon' => 'laser',        'armor' => 'medium', 'shield' => 'medium', 'engine' => ''),
        417 => array('weapon' => 'ion',          'armor' => 'medium', 'shield' => 'medium', 'engine' => ''),
        418 => array('weapon' => 'ion',          'armor' => 'heavy',  'shield' => 'heavy',  'engine' => ''),
        419 => array('weapon' => 'ion',          'armor' => 'heavy',  'shield' => 'heavy',  'engine' => ''),
    );

    private static $weaponCodes = array(
        'laser' => 1,
        'ion' => 2,
        'plasma' => 3,
        'gravitational' => 4,
    );

    private static $classCodes = array(
        'armor' => array('light' => 5, 'medium' => 6, 'heavy' => 7),
        'shield' => array('light' => 8, 'medium' => 9, 'heavy' => 10),
        'engine' => array('combustion' => 11, 'impulse' => 12, 'hyperspace' => 13),
        'conveyor' => array('light' => 14, 'medium' => 15, 'heavy' => 16),
    );

    private static $lightPool = array(1, 5, 8, 11, 14, 17, 18, 19);
    private static $mediumPool = array(2, 3, 6, 9, 12, 15);
    private static $heavyPool = array(4, 7, 10, 13, 16);

    public static function getCatalog()
    {
        if (self::$catalog !== NULL) {
            return self::$catalog;
        }

        $c = array();
        $c[1] = self::def('Laser weapons', 'weapon', 'laser', 0.75);
        $c[2] = self::def('Ion cannon', 'weapon', 'ion', 0.75);
        $c[3] = self::def('Plasma gun', 'weapon', 'plasma', 0.75);
        $c[4] = self::def('Gravitational gun', 'weapon', 'gravitational', 0.75);
        $c[5] = self::def('Light armor', 'armor', 'light', 0.6);
        $c[6] = self::def('Medium armor', 'armor', 'medium', 0.5);
        $c[7] = self::def('Heavy armor', 'armor', 'heavy', 0.4);
        $c[8] = self::def('Light shields', 'shield', 'light', 0.6);
        $c[9] = self::def('Medium shields', 'shield', 'medium', 0.5);
        $c[10] = self::def('Heavy shields', 'shield', 'heavy', 0.4);
        $c[11] = self::def('Jet engine', 'engine', 'combustion', 0.6, 'combustion');
        $c[12] = self::def('Impulse engine', 'engine', 'impulse', 0.5);
        $c[13] = self::def('Hyperspace engine', 'engine', 'hyperspace', 0.4);
        $c[14] = self::def('Light conveyor', 'conveyor', 'light', 0.6, 'l_conveyer');
        $c[15] = self::def('Average conveyor', 'conveyor', 'medium', 0.5, 'm_conveyer');
        $c[16] = self::def('Heavy conveyor', 'conveyor', 'heavy', 0.4, 'h_conveyer');
        $c[17] = self::def('Metal production', 'production', 'metal', 0.5, 'm_production');
        $c[18] = self::def('Crystal production', 'production', 'crystal', 0.45);
        $c[19] = self::def('Deuterium production', 'production', 'deuterium', 0.4);

        foreach ($c as $code => &$def) {
            $def['code'] = (int)$code;
        }
        unset($def);

        self::$catalog = $c;
        return self::$catalog;
    }

    private static function def($name, $group, $class, $perLevel, $key = '')
    {
        return array(
            'code' => 0,
            'key' => $key,
            'name' => $name,
            'group' => $group,
            'class' => $class,
            'per_level' => (float)$perLevel,
        );
    }

    public static function getUpgrade($code)
    {
        $catalog = self::getCatalog();
        $code = (int)$code;
        return isset($catalog[$code]) ? $catalog[$code] : NULL;
    }

    public static function getUpgradeByKey($key)
    {
        foreach (self::getCatalog() as $def) {
            if ($def['key'] !== '' && $def['key'] === $key) {
                return $def;
            }
        }
        return NULL;
    }

    public static function unitClass($unitCode)
    {
        $unitCode = (int)$unitCode;
        return isset(self::$unitClasses[$unitCode]) ? self::$unitClasses[$unitCode] : NULL;
    }

    public static function tier($points)
    {
        if ($points >= 250000) {
            return 3;
        }
        if ($points >= 50000) {
            return 2;
        }
        if ($points >= 5000) {
            return 1;
        }
        return 0;
    }

    public static function tierMinPoints($tier)
    {
        switch ((int)$tier) {
            case 1:
                return 5000;
            case 2:
                return 50000;
            case 3:
                return 250000;
            default:
                return 0;
        }
    }

    public static function dropPool($tier)
    {
        $tier = (int)$tier;
        if ($tier <= 0) {
            return array();
        }
        $pool = self::$lightPool;
        if ($tier >= 2) {
            $pool = array_merge($pool, self::$mediumPool);
        }
        if ($tier >= 3) {
            $pool = array_merge($pool, self::$heavyPool);
        }
        return $pool;
    }

    /**
     * Resolve one expedition combat-win drop. Rolls are injectable for tests.
     * Returns the chosen upgrade code, or 0 when nothing drops.
     */
    public static function rollDrop($tier, $chanceRoll = NULL, $pickRoll = NULL)
    {
        if ($chanceRoll === NULL) {
            $chanceRoll = mt_rand() / mt_getrandmax();
        }
        if ($pickRoll === NULL) {
            $pickRoll = mt_rand() / mt_getrandmax();
        }
        if ($chanceRoll >= self::DROP_CHANCE) {
            return 0;
        }

        $pool = self::dropPool($tier);
        if (empty($pool)) {
            return 0;
        }
        $pickRoll = max(0.0, min(0.999999999, (float)$pickRoll));
        $index = (int)($pickRoll * count($pool));
        return $pool[$index];
    }

    public static function activationChance($level)
    {
        $level = (int)$level;
        if ($level < self::GUARANTEED_LEVEL) {
            return 1.0;
        }
        $chance = 1.0 - self::CHANCE_STEP * ($level - self::GUARANTEED_LEVEL + 1);
        return $chance < self::CHANCE_FLOOR ? self::CHANCE_FLOOR : $chance;
    }

    /**
     * Resolve one activation. A success raises the level and adds per-level
     * value; a failure leaves the level and shaves 10% of per-level value.
     */
    public static function activate($def, $level, $value, $roll)
    {
        $chance = self::activationChance($level);
        if ($roll < $chance) {
            return array(
                'level' => (int)$level + 1,
                'value' => (float)$value + (float)$def['per_level'],
                'success' => TRUE,
                'chance' => $chance,
            );
        }

        $newValue = (float)$value - self::FAILURE_VALUE_DROP * (float)$def['per_level'];
        return array(
            'level' => (int)$level,
            'value' => $newValue < 0 ? 0.0 : $newValue,
            'success' => FALSE,
            'chance' => $chance,
        );
    }

    public static function getOwned($userId)
    {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return array();
        }
        if (isset(self::$owned[$userId])) {
            return self::$owned[$userId];
        }

        $rows = Database::get()->select(
            "SELECT upgrade_code, level, value FROM %%ACCOUNT_UPGRADES%% WHERE userID = :userId;",
            array(':userId' => $userId)
        );

        $list = array();
        foreach ($rows as $row) {
            $list[(int)$row['upgrade_code']] = array(
                'level' => (int)$row['level'],
                'value' => (float)$row['value'],
            );
        }
        self::$owned[$userId] = $list;
        return $list;
    }

    public static function clearOwnedCache($userId)
    {
        unset(self::$owned[(int)$userId]);
    }

    public static function getItems($userId)
    {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return array();
        }

        $rows = Database::get()->select(
            "SELECT upgrade_code, qty FROM %%UPGRADE_ITEMS%% WHERE userID = :userId AND qty > 0;",
            array(':userId' => $userId)
        );

        $list = array();
        foreach ($rows as $row) {
            $list[(int)$row['upgrade_code']] = (int)$row['qty'];
        }
        return $list;
    }

    private static function valueFor($owned, $code)
    {
        return isset($owned[$code]) ? (float)$owned[$code]['value'] : 0.0;
    }

    /**
     * Additive per-unit combat percentages for the unit's primary weapon,
     * armour and shield classes.
     */
    public static function combatPercents($owned, $unitCode)
    {
        $classes = self::unitClass($unitCode);
        $result = array('Attack' => 0.0, 'Shield' => 0.0, 'Defensive' => 0.0);
        if ($classes === NULL) {
            return $result;
        }

        if (isset(self::$weaponCodes[$classes['weapon']])) {
            $result['Attack'] = self::valueFor($owned, self::$weaponCodes[$classes['weapon']]);
        }
        if (isset(self::$classCodes['armor'][$classes['armor']])) {
            $result['Defensive'] = self::valueFor($owned, self::$classCodes['armor'][$classes['armor']]);
        }
        if ($classes['shield'] !== '' && isset(self::$classCodes['shield'][$classes['shield']])) {
            $result['Shield'] = self::valueFor($owned, self::$classCodes['shield'][$classes['shield']]);
        }
        return $result;
    }

    public static function enginePercent($owned, $unitCode)
    {
        $classes = self::unitClass($unitCode);
        if ($classes === NULL || $classes['engine'] === '') {
            return 0.0;
        }
        return isset(self::$classCodes['engine'][$classes['engine']])
            ? self::valueFor($owned, self::$classCodes['engine'][$classes['engine']])
            : 0.0;
    }

    public static function productionPercent($owned, $resourceId)
    {
        $code = NULL;
        if ((int)$resourceId === 901) {
            $code = 17;
        } elseif ((int)$resourceId === 902) {
            $code = 18;
        } elseif ((int)$resourceId === 903) {
            $code = 19;
        }
        return $code === NULL ? 0.0 : self::valueFor($owned, $code);
    }

    public static function isProductionUpgrade($code)
    {
        return in_array((int)$code, array(17, 18, 19), TRUE);
    }

    public static function factorBonuses($USER)
    {
        $owned = self::getOwned(isset($USER['id']) ? $USER['id'] : 0);
        $units = array();
        foreach (self::$unitClasses as $code => $classes) {
            $bonus = self::combatPercents($owned, $code);
            if ($bonus['Attack'] != 0 || $bonus['Shield'] != 0 || $bonus['Defensive'] != 0) {
                $units[$code] = $bonus;
            }
        }

        return array(
            'units' => $units,
            'metal' => self::productionPercent($owned, 901),
            'crystal' => self::productionPercent($owned, 902),
            'deuterium' => self::productionPercent($owned, 903),
        );
    }

    /**
     * Credit un-activated drawings and bump the "Upgrades found" achievement
     * counter. Used by the expedition drop path.
     */
    public static function addItems($userId, $code, $qty = 1, $countFound = TRUE)
    {
        $def = self::getUpgrade($code);
        $qty = (int)$qty;
        if ($def === NULL || $qty <= 0) {
            return FALSE;
        }

        $db = Database::get();
        $handle = $db->getHandle();
        try {
            $handle->beginTransaction();
            self::addItemsTx($db, $userId, (int)$code, $qty);
            if ($countFound) {
                $db->update(
                    "UPDATE %%USERS%% SET arsenal_found = arsenal_found + :qty WHERE id = :userId;",
                    array(':qty' => $qty, ':userId' => (int)$userId)
                );
            }
            $handle->commit();
        } catch (Exception $e) {
            if ($handle->inTransaction()) {
                $handle->rollBack();
            }
            throw $e;
        }
        return TRUE;
    }

    private static function addItemsTx($db, $userId, $code, $qty)
    {
        $db->insert(
            "INSERT INTO %%UPGRADE_ITEMS%% (userID, upgrade_code, qty)
                VALUES (:userId, :code, :qty)
                ON DUPLICATE KEY UPDATE qty = qty + VALUES(qty);",
            array(':userId' => (int)$userId, ':code' => (int)$code, ':qty' => (int)$qty)
        );
    }

    private static function consumeItemsTx($db, $userId, $code, $qty)
    {
        $row = $db->selectSingle(
            "SELECT qty FROM %%UPGRADE_ITEMS%% WHERE userID = :userId AND upgrade_code = :code FOR UPDATE;",
            array(':userId' => (int)$userId, ':code' => (int)$code)
        );
        if (empty($row) || (int)$row['qty'] < (int)$qty) {
            return FALSE;
        }
        $db->update(
            "UPDATE %%UPGRADE_ITEMS%% SET qty = qty - :qty WHERE userID = :userId AND upgrade_code = :code;",
            array(':qty' => (int)$qty, ':userId' => (int)$userId, ':code' => (int)$code)
        );
        $db->delete(
            "DELETE FROM %%UPGRADE_ITEMS%% WHERE userID = :userId AND upgrade_code = :code AND qty <= 0;",
            array(':userId' => (int)$userId, ':code' => (int)$code)
        );
        return TRUE;
    }

    /**
     * Consume one drawing, roll the activation and persist the result.
     */
    public static function activateItem(&$USER, $code, $roll = NULL)
    {
        $def = self::getUpgrade($code);
        if ($def === NULL) {
            return array('ok' => FALSE, 'reason' => 'unknown');
        }
        if ($roll === NULL) {
            $roll = mt_rand() / mt_getrandmax();
        }

        $userId = (int)$USER['id'];
        $db = Database::get();
        $handle = $db->getHandle();

        try {
            $handle->beginTransaction();
            if (!self::consumeItemsTx($db, $userId, $code, 1)) {
                $handle->rollBack();
                return array('ok' => FALSE, 'reason' => 'items');
            }

            $row = $db->selectSingle(
                "SELECT level, value FROM %%ACCOUNT_UPGRADES%% WHERE userID = :userId AND upgrade_code = :code FOR UPDATE;",
                array(':userId' => $userId, ':code' => (int)$code)
            );
            $level = empty($row) ? 0 : (int)$row['level'];
            $value = empty($row) ? 0.0 : (float)$row['value'];

            $result = self::activate($def, $level, $value, $roll);
            $db->insert(
                "INSERT INTO %%ACCOUNT_UPGRADES%% (userID, upgrade_code, level, value)
                    VALUES (:userId, :code, :level, :value)
                    ON DUPLICATE KEY UPDATE level = VALUES(level), value = VALUES(value);",
                array(
                    ':userId' => $userId,
                    ':code' => (int)$code,
                    ':level' => $result['level'],
                    ':value' => $result['value'],
                )
            );

            if ($result['success']) {
                $db->update(
                    "UPDATE %%USERS%% SET arsenal_activated = arsenal_activated + 1 WHERE id = :userId;",
                    array(':userId' => $userId)
                );
                $USER['arsenal_activated'] = (isset($USER['arsenal_activated']) ? (int)$USER['arsenal_activated'] : 0) + 1;
            }

            $handle->commit();
        } catch (Exception $e) {
            if ($handle->inTransaction()) {
                $handle->rollBack();
            }
            throw $e;
        }

        self::clearOwnedCache($userId);

        return array(
            'ok' => TRUE,
            'code' => (int)$code,
            'name' => $def['name'],
            'success' => $result['success'],
            'chance' => $result['chance'],
            'level' => $result['level'],
            'bonus' => $result['value'],
        );
    }

    /**
     * List drawings on the market. The stored price is the total Antimatter
     * price (rate * amount).
     */
    public static function listLot($userId, $code, $amount, $rate)
    {
        $def = self::getUpgrade($code);
        if ($def === NULL) {
            return array('ok' => FALSE, 'reason' => 'unknown');
        }
        $amount = (int)$amount;
        $rate = (int)$rate;
        if ($amount < 1 || $amount > self::MAX_LOT_AMOUNT || $rate < 1 || $rate > self::MAX_LOT_RATE) {
            return array('ok' => FALSE, 'reason' => 'invalid');
        }

        $db = Database::get();
        $handle = $db->getHandle();
        $price = $amount * $rate;

        try {
            $handle->beginTransaction();
            if (!self::consumeItemsTx($db, $userId, $code, $amount)) {
                $handle->rollBack();
                return array('ok' => FALSE, 'reason' => 'items');
            }
            $db->insert(
                "INSERT INTO %%MARKET_LOTS%% (seller_id, upgrade_code, amount, price, created_at, expires_at)
                    VALUES (:seller, :code, :amount, :price, :created, :expires);",
                array(
                    ':seller' => (int)$userId,
                    ':code' => (int)$code,
                    ':amount' => $amount,
                    ':price' => $price,
                    ':created' => TIMESTAMP,
                    ':expires' => TIMESTAMP + self::LOT_HOURS * 3600,
                )
            );
            $lotId = (int)$db->lastInsertId();
            $handle->commit();
        } catch (Exception $e) {
            if ($handle->inTransaction()) {
                $handle->rollBack();
            }
            throw $e;
        }

        return array('ok' => TRUE, 'id' => $lotId, 'amount' => $amount, 'rate' => $rate, 'price' => $price);
    }

    public static function marketLots()
    {
        return self::lots(
            "SELECT id, seller_id, upgrade_code, amount, price, created_at, expires_at
                FROM %%MARKET_LOTS%% WHERE expires_at > :now ORDER BY price ASC, id ASC;",
            array(':now' => TIMESTAMP)
        );
    }

    public static function ownLots($userId)
    {
        return self::lots(
            "SELECT id, seller_id, upgrade_code, amount, price, created_at, expires_at
                FROM %%MARKET_LOTS%% WHERE seller_id = :userId AND expires_at > :now ORDER BY id ASC;",
            array(':userId' => (int)$userId, ':now' => TIMESTAMP)
        );
    }

    private static function lots($sql, $params)
    {
        $rows = Database::get()->select($sql, $params);
        $out = array();
        foreach ($rows as $row) {
            $def = self::getUpgrade($row['upgrade_code']);
            $out[] = array(
                'id' => (int)$row['id'],
                'sellerId' => (int)$row['seller_id'],
                'code' => (int)$row['upgrade_code'],
                'name' => $def === NULL ? '#' . $row['upgrade_code'] : $def['name'],
                'amount' => (int)$row['amount'],
                'price' => (int)$row['price'],
                'rate' => (int)$row['amount'] > 0 ? (int)round($row['price'] / $row['amount']) : 0,
                'createdAt' => (int)$row['created_at'],
                'expiresAt' => (int)$row['expires_at'],
            );
        }
        return $out;
    }

    /**
     * Buy a lot atomically: charge buyer, credit seller, transfer drawings and
     * remove the lot.
     */
    public static function buyLot(&$USER, $lotId)
    {
        $lotId = (int)$lotId;
        $buyerId = (int)$USER['id'];
        $db = Database::get();
        $handle = $db->getHandle();

        try {
            $handle->beginTransaction();
            $lot = $db->selectSingle(
                "SELECT seller_id, upgrade_code, amount, price, expires_at FROM %%MARKET_LOTS%% WHERE id = :id FOR UPDATE;",
                array(':id' => $lotId)
            );
            if (empty($lot)) {
                $handle->rollBack();
                return array('ok' => FALSE, 'reason' => 'missing');
            }
            if ((int)$lot['expires_at'] <= TIMESTAMP) {
                self::returnLotTx($db, $lotId, $lot);
                $handle->commit();
                return array('ok' => FALSE, 'reason' => 'expired');
            }
            if ((int)$lot['seller_id'] === $buyerId) {
                $handle->rollBack();
                return array('ok' => FALSE, 'reason' => 'own');
            }

            $db->update(
                "UPDATE %%USERS%% SET antimatter = antimatter - :price WHERE id = :buyer AND antimatter >= :price;",
                array(':price' => (int)$lot['price'], ':buyer' => $buyerId)
            );
            if ($db->rowCount() < 1) {
                $handle->rollBack();
                return array('ok' => FALSE, 'reason' => 'antimatter');
            }
            $db->update(
                "UPDATE %%USERS%% SET antimatter = antimatter + :price WHERE id = :seller;",
                array(':price' => (int)$lot['price'], ':seller' => (int)$lot['seller_id'])
            );
            self::addItemsTx($db, $buyerId, (int)$lot['upgrade_code'], (int)$lot['amount']);
            $db->insert(
                "INSERT INTO %%MARKET_PURCHASES%% (lot_id, buyer_id, seller_id, upgrade_code, amount, price, created_at)
                    VALUES (:lotId, :buyer, :seller, :code, :amount, :price, :time);",
                array(
                    ':lotId' => $lotId,
                    ':buyer' => $buyerId,
                    ':seller' => (int)$lot['seller_id'],
                    ':code' => (int)$lot['upgrade_code'],
                    ':amount' => (int)$lot['amount'],
                    ':price' => (int)$lot['price'],
                    ':time' => TIMESTAMP,
                )
            );
            $db->delete("DELETE FROM %%MARKET_LOTS%% WHERE id = :id;", array(':id' => $lotId));
            $handle->commit();
        } catch (Exception $e) {
            if ($handle->inTransaction()) {
                $handle->rollBack();
            }
            throw $e;
        }

        $USER['antimatter'] = (float)$USER['antimatter'] - (int)$lot['price'];
        $def = self::getUpgrade($lot['upgrade_code']);

        return array(
            'ok' => TRUE,
            'name' => $def === NULL ? '#' . $lot['upgrade_code'] : $def['name'],
            'amount' => (int)$lot['amount'],
            'price' => (int)$lot['price'],
        );
    }

    public static function removeLot($userId, $lotId)
    {
        $db = Database::get();
        $handle = $db->getHandle();

        try {
            $handle->beginTransaction();
            $lot = $db->selectSingle(
                "SELECT seller_id, upgrade_code, amount, expires_at FROM %%MARKET_LOTS%% WHERE id = :id FOR UPDATE;",
                array(':id' => (int)$lotId)
            );
            if (empty($lot) || (int)$lot['seller_id'] !== (int)$userId) {
                $handle->rollBack();
                return array('ok' => FALSE, 'reason' => 'missing');
            }
            if ((int)$lot['expires_at'] <= TIMESTAMP) {
                self::returnLotTx($db, (int)$lotId, $lot);
                $handle->commit();
                return array('ok' => FALSE, 'reason' => 'expired');
            }
            self::addItemsTx($db, (int)$userId, (int)$lot['upgrade_code'], (int)$lot['amount']);
            $db->delete("DELETE FROM %%MARKET_LOTS%% WHERE id = :id;", array(':id' => (int)$lotId));
            $handle->commit();
        } catch (Exception $e) {
            if ($handle->inTransaction()) {
                $handle->rollBack();
            }
            throw $e;
        }

        return array('ok' => TRUE, 'amount' => (int)$lot['amount']);
    }

    private static function returnLotTx($db, $lotId, $lot)
    {
        self::addItemsTx($db, (int)$lot['seller_id'], (int)$lot['upgrade_code'], (int)$lot['amount']);
        $db->delete("DELETE FROM %%MARKET_LOTS%% WHERE id = :id;", array(':id' => (int)$lotId));
    }

    /**
     * Return every expired lot to its seller. Safe to call on page loads.
     */
    public static function expireLots()
    {
        $rows = Database::get()->select(
            "SELECT id FROM %%MARKET_LOTS%% WHERE expires_at <= :now;",
            array(':now' => TIMESTAMP)
        );

        $count = 0;
        foreach ($rows as $row) {
            $db = Database::get();
            $handle = $db->getHandle();
            try {
                $handle->beginTransaction();
                $lot = $db->selectSingle(
                    "SELECT seller_id, upgrade_code, amount, expires_at FROM %%MARKET_LOTS%% WHERE id = :id FOR UPDATE;",
                    array(':id' => (int)$row['id'])
                );
                if (!empty($lot)) {
                    self::returnLotTx($db, (int)$row['id'], $lot);
                    $count++;
                }
                $handle->commit();
            } catch (Exception $e) {
                if ($handle->inTransaction()) {
                    $handle->rollBack();
                }
                throw $e;
            }
        }
        return $count;
    }
}
