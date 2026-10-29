ALTER TABLE %PREFIX%users
  ADD `achievement_points` int(11) unsigned NOT NULL DEFAULT '0',
  ADD `achievement_points_spent` int(11) unsigned NOT NULL DEFAULT '0',
  ADD `antimatter` double(255,0) NOT NULL DEFAULT '0',
  ADD `gov_level_701` smallint(5) unsigned NOT NULL DEFAULT '0',
  ADD `gov_level_702` smallint(5) unsigned NOT NULL DEFAULT '0',
  ADD `gov_level_703` smallint(5) unsigned NOT NULL DEFAULT '0',
  ADD `gov_level_704` smallint(5) unsigned NOT NULL DEFAULT '0',
  ADD `gov_level_705` smallint(5) unsigned NOT NULL DEFAULT '0',
  ADD `gov_level_706` smallint(5) unsigned NOT NULL DEFAULT '0',
  ADD `gov_level_707` smallint(5) unsigned NOT NULL DEFAULT '0';
CREATE TABLE %PREFIX%achievements (
  `userID` int(11) unsigned NOT NULL,
  `achievement` varchar(64) NOT NULL,
  `level` int(11) unsigned NOT NULL DEFAULT '0',
  `progress` bigint(20) unsigned NOT NULL DEFAULT '0',
  `updated_at` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`userID`,`achievement`),
  KEY `achievement` (`achievement`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
