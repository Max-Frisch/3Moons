<?php

/**
 * Online Bonus page: peaceful level, passive bonuses and the Bonus button.
 *
 * The button payout is the Academy-point source captured live: Dark Matter,
 * peaceful online progress and "Experience points" (Academy points).
 */
class ShowBonusPage extends AbstractGamePage
{
    public static $requireModule = 0;
    public static $defaultController = 'show';

    public function __construct()
    {
        parent::__construct();
    }

    private function setNotice($notice)
    {
        $_SESSION['bonusNotice'] = $notice;
    }

    private function getNotice()
    {
        if (empty($_SESSION['bonusNotice'])) {
            return NULL;
        }
        $notice = $_SESSION['bonusNotice'];
        unset($_SESSION['bonusNotice']);
        return $notice;
    }

    public function show()
    {
        global $USER, $LNG;

        $action = HTTP::_GP('action', '');
        if ($action === 'claim' && $_SERVER['REQUEST_METHOD'] === 'POST' && $USER['urlaubs_modus'] == 0) {
            $result = Progression::claimBonus($USER);
            if ($result['ok']) {
                $this->setNotice(array(
                    'type' => 'ok',
                    'reward' => $result['reward'],
                ));
            } else {
                $this->setNotice(array(
                    'type' => 'error',
                    'reason' => $result['reason'],
                    'ready_seconds' => isset($result['ready_seconds']) ? $result['ready_seconds'] : 0,
                ));
            }
            $this->redirectTo('game.php?page=bonus');
        }

        $state = Progression::bonusState($USER);
        $active = Progression::premiumActive($USER);

        $premium = array();
        foreach ($active as $item => $data) {
            $nameKey = 'pr_' . $item;
            $premium[] = array(
                'item' => $item,
                'name' => isset($LNG[$nameKey]) ? $LNG[$nameKey] : $item,
                'value' => $data['value'],
                'remaining' => pretty_time(max(0, $data['expires_at'] - TIMESTAMP)),
            );
        }

        $this->assign(array(
            'bonusNotice' => $this->getNotice(),
            'peacefulLevel' => $state['level'],
            'peacefulProgress' => number_format($state['progress'], 2, '.', ''),
            'peacefulNext' => pretty_time($state['next_seconds']),
            'peacefulBonuses' => $state['bonuses'],
            'bonusReady' => $state['ready'],
            'bonusReadySeconds' => pretty_time($state['ready_seconds']),
            'bonusReward' => $state['reward'],
            'premiumBonuses' => $premium,
            'bonusMultiplier' => Progression::premiumBonusMultiplier($USER),
            'bonusCooldown' => Progression::premiumBonusCooldown($USER),
        ));

        $this->display('page.bonus.default.tpl');
    }
}
