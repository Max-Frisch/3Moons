ALTER TABLE %PREFIX%users
  ADD `arsenal_found` int(11) unsigned NOT NULL DEFAULT '0',
  ADD `arsenal_activated` int(11) unsigned NOT NULL DEFAULT '0';

CREATE TABLE %PREFIX%account_upgrades (
  `userID` int(11) unsigned NOT NULL,
  `upgrade_code` tinyint(3) unsigned NOT NULL,
  `level` int(11) unsigned NOT NULL DEFAULT '0',
  `value` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`userID`,`upgrade_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE %PREFIX%upgrade_items (
  `userID` int(11) unsigned NOT NULL,
  `upgrade_code` tinyint(3) unsigned NOT NULL,
  `qty` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`userID`,`upgrade_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE %PREFIX%market_lots (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `seller_id` int(11) unsigned NOT NULL,
  `upgrade_code` tinyint(3) unsigned NOT NULL,
  `amount` int(11) unsigned NOT NULL,
  `price` int(11) unsigned NOT NULL,
  `created_at` int(11) unsigned NOT NULL DEFAULT '0',
  `expires_at` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `expires_at` (`expires_at`),
  KEY `seller_id` (`seller_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE %PREFIX%market_purchases (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `lot_id` int(11) unsigned NOT NULL,
  `buyer_id` int(11) unsigned NOT NULL,
  `seller_id` int(11) unsigned NOT NULL,
  `upgrade_code` tinyint(3) unsigned NOT NULL,
  `amount` int(11) unsigned NOT NULL,
  `price` int(11) unsigned NOT NULL,
  `created_at` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `buyer_id` (`buyer_id`),
  KEY `seller_id` (`seller_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
