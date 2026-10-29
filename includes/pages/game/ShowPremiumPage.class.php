<?php

/**
 * Premium page: timed account bonuses bought with Antimatter.
 *
 * Catalog values, growth factors and the cost formula are copied from the live
 * page=premium pblist / premium.js capture. The progression-related bonuses
 * (resource, storage, build/research speed, queue slots, peaceful/combat XP,
 * Bonus button, fuel) are wired; the remaining entries are purchasable and
 * stored for later phases.
 */
class ShowPremiumPage extends AbstractGamePage
{
    public static $requireModule = 0;
    public static $defaultController = 'show';

    public function __construct()
    {
        parent::__construct();
    }

    private function setNotice($notice)
    {
        $_SESSION['premiumNotice'] = $notice;
    }

    private function getNotice()
    {
        if (empty($_SESSION['premiumNotice'])) {
            return NULL;
        }
        $notice = $_SESSION['premiumNotice'];
        unset($_SESSION['premiumNotice']);
        return $notice;
    }

    public function show()
    {
        global $USER, $LNG;

        $action = HTTP::_GP('action', '');
        if ($action === 'buy' && $_SERVER['REQUEST_METHOD'] === 'POST' && $USER['urlaubs_modus'] == 0) {
            $item = HTTP::_GP('item', '');
            $count = HTTP::_GP('count', 0);
            $days = HTTP::_GP('days', 0);
            $result = Progression::buyPremium($USER, $item, $count, $days);
            if ($result['ok']) {
                $this->setNotice(array(
                    'type' => 'ok',
                    'item' => $item,
                    'cost' => $result['cost'],
                    'days' => $result['days'],
                ));
            } else {
                $this->setNotice(array('type' => 'error', 'reason' => $result['reason']));
            }
            $this->redirectTo('game.php?page=premium');
        }

        $catalog = Progression::getPremiumCatalog();
        $active = Progression::premiumActive($USER);

        $items = array();
        $scriptData = array();
        foreach ($catalog as $id => $def) {
            $isActive = isset($active[$id]);
            $activeValue = $isActive ? (float)$active[$id]['value'] : 0.0;
            $expiresAt = $isActive ? (int)$active[$id]['expires_at'] : 0;

            $defaultCount = $def['min'] > 0 ? (int)$def['min'] : 1;
            $count = $def['kind'] === 'ext' ? (int)$def['fixed'] : (int)max($defaultCount, min($def['max'], $activeValue > 0 ? $activeValue : $defaultCount));
            $days = 1;

            $items[] = array(
                'id' => $id,
                'name' => $LNG[$def['name']],
                'description' => $LNG[$def['desc']],
                'kind' => $def['kind'],
                'fixed' => (int)$def['fixed'],
                'count' => $count,
                'min' => (int)$def['min'],
                'max' => (int)$def['max'],
                'unit' => $def['unit'],
                'active' => $isActive,
                'activeValue' => $activeValue,
                'remaining' => $isActive ? pretty_time(max(0, $expiresAt - TIMESTAMP)) : '',
                'price' => Progression::premiumCost($id, $count, $days),
                'days' => $days,
            );

            $scriptData[$id] = array(
                'cost' => (float)$def['cost'],
                'factor' => (float)$def['factor'],
                'step' => (float)$def['step'],
                'range' => (int)$def['min'],
                'min' => (int)$def['min'],
                'max' => (int)$def['max'],
                'kind' => $def['kind'],
                'fixed' => (int)$def['fixed'],
            );
        }

        $this->assign(array(
            'premiumNotice' => $this->getNotice(),
            'premiumItems' => $items,
            'premiumJson' => json_encode($scriptData),
            'antimatter' => pretty_number($USER['antimatter']),
            'antimatterRaw' => (float)$USER['antimatter'],
        ));

        $this->display('page.premium.default.tpl');
    }
}
