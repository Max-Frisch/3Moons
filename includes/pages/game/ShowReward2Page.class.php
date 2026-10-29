<?php

/**
 * Voucher System (live page=reward2). Codes are issued out-of-band (SQL/admin)
 * and redeem for resources/currency; a code can be limited by uses/expiry.
 */
class ShowReward2Page extends AbstractGamePage
{
	public static $requireModule = 0;

	function __construct()
	{
		parent::__construct();
	}

	public function show()
	{
		global $USER, $LNG;

		$errorKey = HTTP::_GP('error', '');
		$error = $errorKey !== '' && isset($LNG['bm_error'][$errorKey]) ? $LNG['bm_error'][$errorKey] : '';

		$this->assign(array(
			'done'		=> HTTP::_GP('done', 0) == 1,
			'error'		=> $error,
		));

		$this->display('page.reward2.default.tpl');
	}

	public function redeem()
	{
		global $USER, $PLANET, $LNG;

		$code = strtoupper(trim((string)HTTP::_GP('code', '')));
		if ($code === '' || strlen($code) > 64 || !preg_match('/^[A-Z0-9_-]+$/', $code)) {
			$this->redirectTo('game.php?page=reward2&error=voucher');
		}

		$db = Database::get();
		$config = Config::get();
		$handle = $db->getHandle();

		try {
			$handle->beginTransaction();
			$voucher = $db->selectSingle(
				"SELECT * FROM %%VOUCHERS%% WHERE code = :code FOR UPDATE;",
				array(':code' => $code)
			);

			if (empty($voucher)
				|| (int)$voucher['uses'] >= (int)$voucher['max_uses']
				|| ((int)$voucher['expires_at'] > 0 && (int)$voucher['expires_at'] < TIMESTAMP)) {
				$handle->rollBack();
				$this->redirectTo('game.php?page=reward2&error=voucher');
			}

			$dm = (float)$voucher['reward_darkmatter'];
			$am = (float)$voucher['reward_antimatter'];
			$metal = (float)$voucher['reward_metal'];
			$crystal = (float)$voucher['reward_crystal'];
			$deuterium = (float)$voucher['reward_deuterium'];

			$db->update(
				"UPDATE %%USERS%% SET darkmatter = darkmatter + :dm, antimatter = antimatter + :am WHERE id = :userId;",
				array(':dm' => $dm, ':am' => $am, ':userId' => $USER['id'])
			);
			$db->update(
				"UPDATE %%VOUCHERS%% SET uses = uses + 1 WHERE code = :code;",
				array(':code' => $code)
			);

			$maxOverflow = (float)$config->max_overflow;
			$newMetal = max((float)$PLANET['metal'], min((float)$PLANET['metal_max'] * $maxOverflow, (float)$PLANET['metal'] + $metal));
			$newCrystal = max((float)$PLANET['crystal'], min((float)$PLANET['crystal_max'] * $maxOverflow, (float)$PLANET['crystal'] + $crystal));
			$newDeuterium = max((float)$PLANET['deuterium'], min((float)$PLANET['deuterium_max'] * $maxOverflow, (float)$PLANET['deuterium'] + $deuterium));

			if ($metal > 0 || $crystal > 0 || $deuterium > 0) {
				$db->update(
					"UPDATE %%PLANETS%% SET metal = :metal, crystal = :crystal, deuterium = :deuterium WHERE id = :planetId;",
					array(
						':metal'		=> $newMetal,
						':crystal'		=> $newCrystal,
						':deuterium'	=> $newDeuterium,
						':planetId'		=> $PLANET['id'],
					)
				);
			}

			$handle->commit();
		} catch (Exception $e) {
			if ($handle->inTransaction()) {
				$handle->rollBack();
			}
			$this->redirectTo('game.php?page=reward2&error=unavailable');
		}

		$USER['darkmatter'] = (float)$USER['darkmatter'] + $dm;
		$USER['antimatter'] = (float)$USER['antimatter'] + $am;
		$PLANET['metal'] = $newMetal;
		$PLANET['crystal'] = $newCrystal;
		$PLANET['deuterium'] = $newDeuterium;

		$this->redirectTo('game.php?page=reward2&done=1');
	}
}
