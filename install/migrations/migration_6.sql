ALTER TABLE %PREFIX%users
  ADD `academy_points` int(11) unsigned NOT NULL DEFAULT '0',
  ADD `academy_points_spent` int(11) unsigned NOT NULL DEFAULT '0';

CREATE TABLE %PREFIX%academy (
  `userID` int(11) unsigned NOT NULL,
  `skill` varchar(8) NOT NULL,
  `level` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`userID`,`skill`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

UPDATE %PREFIX%users
  SET academy_points = achievement_points + achievement_points_spent;
