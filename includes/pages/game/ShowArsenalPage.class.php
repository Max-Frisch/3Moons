<?php

/**
 * Niburuspace-style Arsenal page.
 *
 * Catalog and activation rules live in includes/classes/class.Arsenal.php.
 */
class ShowArsenalPage extends AbstractGamePage
{
    public static $requireModule = 0;
    public static $defaultController = 'show';

    public function __construct()
    {
        parent::__construct();
    }

    private function setNotice($notice)
    {
        $_SESSION['arsenalNotice'] = $notice;
    }

    private function getNotice()
    {
        if (empty($_SESSION['arsenalNotice'])) {
            return NULL;
        }
        $notice = $_SESSION['arsenalNotice'];
        unset($_SESSION['arsenalNotice']);
        return $notice;
    }

    private function buildUpgrades($owned, $items)
    {
        global $LNG;

        $groups = array(
            'weapon' => 'ars_group_weapon',
            'armor' => 'ars_group_armor',
            'shield' => 'ars_group_shield',
            'engine' => 'ars_group_engine',
            'conveyor' => 'ars_group_conveyor',
            'production' => 'ars_group_production',
        );

        $out = array();
        foreach (Arsenal::getCatalog() as $code => $def) {
            $level = isset($owned[$code]) ? (int)$owned[$code]['level'] : 0;
            $bonus = isset($owned[$code]) ? (float)$owned[$code]['value'] : 0.0;
            $available = isset($items[$code]) ? (int)$items[$code] : 0;

            $out[] = array(
                'code' => $code,
                'name' => $def['name'],
                'key' => $def['key'],
                'group' => isset($groups[$def['group']]) ? $LNG[$groups[$def['group']]] : $def['group'],
                'level' => $level,
                'bonus' => $bonus,
                'nextBonus' => (float)$def['per_level'],
                'available' => $available,
                'canActivate' => $available > 0,
            );
        }
        return $out;
    }

    public function show()
    {
        global $USER, $LNG;

        $mode = HTTP::_GP('mode', 'show');
        if ($mode === 'send' && $_SERVER['REQUEST_METHOD'] === 'POST' && $USER['urlaubs_modus'] == 0) {
            $code = (int)HTTP::_GP('code', 0);
            if ($code <= 0) {
                $def = Arsenal::getUpgradeByKey(HTTP::_GP('greid', ''));
                $code = $def === NULL ? 0 : (int)$def['code'];
            }

            $result = Arsenal::activateItem($USER, $code);
            if ($result['ok']) {
                $this->setNotice(array(
                    'type' => 'ok',
                    'success' => $result['success'],
                    'name' => $result['name'],
                    'chance' => $result['chance'],
                    'level' => $result['level'],
                    'bonus' => $result['bonus'],
                ));
            } else {
                $this->setNotice(array('type' => 'error', 'reason' => $result['reason']));
            }

            $this->redirectTo('game.php?page=arsenal');
        }

        $owned = Arsenal::getOwned($USER['id']);
        $items = Arsenal::getItems($USER['id']);

        $this->assign(array(
            'arsenalUpgrades' => $this->buildUpgrades($owned, $items),
            'arsenalNotice' => $this->getNotice(),
            'arsenalFound' => pretty_number(isset($USER['arsenal_found']) ? $USER['arsenal_found'] : 0),
            'arsenalActivated' => pretty_number(isset($USER['arsenal_activated']) ? $USER['arsenal_activated'] : 0),
        ));

        $this->display('page.arsenal.default.tpl');
    }
}
