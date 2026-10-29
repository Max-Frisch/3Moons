<?php

/**
 * Summary helpers for stored combat reports.
 *
 * The live client shows a per-side Start / Lost / Left table next to the
 * round-by-round detail. 2Moons only stores the round snapshots, so the
 * summary is derived from the first and last stored round. Captured layout:
 * neoxnova-ops/tools/explorer/data/1687-real-report.html.
 */
class CombatReport
{
	public static function summarize($report)
	{
		$summary = array(
			'attacker'	=> self::emptySide(),
			'defender'	=> self::emptySide(),
		);

		if (!is_array($report) || empty($report['rounds']) || !is_array($report['rounds'])) {
			return $summary;
		}

		$rounds			= array_values($report['rounds']);
		$firstRound		= reset($rounds);
		$lastRound		= end($rounds);

		foreach (array('attacker', 'defender') as $side) {
			$start	= self::sideShips($firstRound, $side);
			$left	= self::sideShips($lastRound, $side);
			$keys	= array_unique(array_merge(array_keys($start), array_keys($left)));
			sort($keys);

			$startTotal	= 0;
			$leftTotal	= 0;
			$lostTotal	= 0;

			foreach ($keys as $shipID) {
				$startAmount	= isset($start[$shipID]) ? $start[$shipID] : 0;
				$leftAmount		= isset($left[$shipID]) ? $left[$shipID] : 0;
				$lostAmount		= max(0, $startAmount - $leftAmount);

				$summary[$side]['start'][$shipID]	= $startAmount;
				$summary[$side]['left'][$shipID]	= $leftAmount;
				$summary[$side]['lost'][$shipID]	= $lostAmount;

				$startTotal	+= $startAmount;
				$leftTotal	+= $leftAmount;
				$lostTotal	+= $lostAmount;
			}

			$summary[$side]['startTotal']	= $startTotal;
			$summary[$side]['leftTotal']	= $leftTotal;
			$summary[$side]['lostTotal']	= $lostTotal;
		}

		return $summary;
	}

	public static function lossShare($summary)
	{
		$attacker	= isset($summary['attacker']['lostTotal']) ? (int) $summary['attacker']['lostTotal'] : 0;
		$defender	= isset($summary['defender']['lostTotal']) ? (int) $summary['defender']['lostTotal'] : 0;
		$total		= $attacker + $defender;

		if ($total <= 0) {
			return array('attacker' => 0, 'defender' => 0);
		}

		return array(
			'attacker'	=> round($attacker / $total * 100, 2),
			'defender'	=> round($defender / $total * 100, 2),
		);
	}

	public static function ensurePlayers($report)
	{
		if (!is_array($report) || empty($report['rounds']) || !is_array($report['rounds'])) {
			return $report;
		}

		if (empty($report['players']) || !is_array($report['players'])) {
			$report['players'] = array();
		}

		foreach ($report['rounds'] as $round) {
			foreach (array('attacker', 'defender') as $side) {
				if (empty($round[$side]) || !is_array($round[$side])) {
					continue;
				}

				foreach ($round[$side] as $player) {
					$userID = isset($player['userID']) ? $player['userID'] : 0;
					if (!isset($report['players'][$userID])) {
						$report['players'][$userID] = array(
							'name'	=> 'Unknown',
							'koords'=> array(0, 0, 0, 1),
							'tech'	=> array(0, 0, 0),
						);
					}
				}
			}
		}

		return $report;
	}

	public static function winner($result)
	{
		if ($result === 'a') {
			return 'attacker';
		}

		if ($result === 'r') {
			return 'defender';
		}

		return 'draw';
	}

	private static function emptySide()
	{
		return array(
			'start'		=> array(),
			'lost'		=> array(),
			'left'		=> array(),
			'startTotal'=> 0,
			'lostTotal'	=> 0,
			'leftTotal'	=> 0,
		);
	}

	private static function sideShips($round, $side)
	{
		$ships = array();

		if (!is_array($round) || empty($round[$side]) || !is_array($round[$side])) {
			return $ships;
		}

		foreach ($round[$side] as $player) {
			if (empty($player['ships']) || !is_array($player['ships'])) {
				continue;
			}

			foreach ($player['ships'] as $shipID => $shipData) {
				$amount	= isset($shipData[0]) ? (int) $shipData[0] : 0;
				if ($amount <= 0) {
					continue;
				}

				$ships[$shipID] = (isset($ships[$shipID]) ? $ships[$shipID] : 0) + $amount;
			}
		}

		ksort($ships);

		return $ships;
	}
}
