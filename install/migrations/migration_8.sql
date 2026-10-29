ALTER TABLE %PREFIX%users
  ADD `peaceful_seconds` bigint(20) unsigned NOT NULL DEFAULT '0',
  ADD `peaceful_seen_at` int(11) unsigned NOT NULL DEFAULT '0',
  ADD `combat_xp` bigint(20) unsigned NOT NULL DEFAULT '0',
  ADD `bonus_button_ready_at` int(11) unsigned NOT NULL DEFAULT '0',
  ADD `bonus_last_click_at` int(11) unsigned NOT NULL DEFAULT '0';

CREATE TABLE %PREFIX%premium_bonuses (
  `userID` int(11) unsigned NOT NULL,
  `item` varchar(32) NOT NULL,
  `value` double NOT NULL DEFAULT '0',
  `expires_at` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`userID`,`item`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
