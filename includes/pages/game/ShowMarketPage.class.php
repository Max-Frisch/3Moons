<?php

/**
 * Niburuspace-style upgrade auction ("Market").
 *
 * Lots are un-activated Arsenal drawings sold for Antimatter. Data and
 * transactional rules live in includes/classes/class.Arsenal.php.
 */
class ShowMarketPage extends AbstractGamePage
{
    public static $requireModule = 0;
    public static $defaultController = 'show';

    public function __construct()
    {
        parent::__construct();
    }

    private function setNotice($notice)
    {
        $_SESSION['marketNotice'] = $notice;
    }

    private function getNotice()
    {
        if (empty($_SESSION['marketNotice'])) {
            return NULL;
        }
        $notice = $_SESSION['marketNotice'];
        unset($_SESSION['marketNotice']);
        return $notice;
    }

    private function buildSellOptions($items)
    {
        $out = array();
        foreach (Arsenal::getCatalog() as $code => $def) {
            $out[] = array(
                'code' => $code,
                'name' => $def['name'],
                'available' => isset($items[$code]) ? (int)$items[$code] : 0,
            );
        }
        return $out;
    }

    public function show()
    {
        global $USER, $LNG;

        $mode = HTTP::_GP('mode', 'show');
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $USER['urlaubs_modus'] == 0) {
            if ($mode === 'sellUpgrades') {
                $result = Arsenal::listLot(
                    $USER['id'],
                    HTTP::_GP('type', 0),
                    HTTP::_GP('amount', 0),
                    HTTP::_GP('rate', 0)
                );
                $this->setNotice($result['ok']
                    ? array('type' => 'listed', 'amount' => $result['amount'], 'price' => pretty_number($result['price']))
                    : array('type' => 'error', 'reason' => $result['reason']));
            } elseif ($mode === 'BuyUpgrade') {
                $result = Arsenal::buyLot($USER, HTTP::_GP('id', 0));
                $this->setNotice($result['ok']
                    ? array('type' => 'bought', 'name' => $result['name'], 'amount' => $result['amount'], 'price' => pretty_number($result['price']))
                    : array('type' => 'error', 'reason' => $result['reason']));
            } elseif ($mode === 'remove') {
                $result = Arsenal::removeLot($USER['id'], HTTP::_GP('id', 0));
                $this->setNotice($result['ok']
                    ? array('type' => 'removed', 'amount' => $result['amount'])
                    : array('type' => 'error', 'reason' => $result['reason']));
            }

            $this->redirectTo('game.php?page=market');
        }

        Arsenal::expireLots();

        $lots = Arsenal::marketLots();
        foreach ($lots as &$lot) {
            $lot['priceText'] = pretty_number($lot['price']);
            $lot['rateText'] = pretty_number($lot['rate']);
        }
        unset($lot);

        $ownLots = Arsenal::ownLots($USER['id']);
        foreach ($ownLots as &$lot) {
            $lot['priceText'] = pretty_number($lot['price']);
            $lot['rateText'] = pretty_number($lot['rate']);
        }
        unset($lot);

        $this->assign(array(
            'marketLots' => $lots,
            'marketOwnLots' => $ownLots,
            'marketSellOptions' => $this->buildSellOptions(Arsenal::getItems($USER['id'])),
            'marketNotice' => $this->getNotice(),
            'marketAntimatter' => pretty_number(isset($USER['antimatter']) ? $USER['antimatter'] : 0),
            'marketLotHours' => Arsenal::LOT_HOURS,
            'marketMaxAmount' => Arsenal::MAX_LOT_AMOUNT,
            'marketMaxRate' => Arsenal::MAX_LOT_RATE,
        ));

        $this->display('page.market.default.tpl');
    }
}
