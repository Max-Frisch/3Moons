ALTER TABLE %PREFIX%planets
  ADD `kollaider` tinyint(3) unsigned NOT NULL DEFAULT '0',
  ADD `fields_bought` smallint(5) unsigned NOT NULL DEFAULT '0',
  ADD `last_relocation` int(11) unsigned NOT NULL DEFAULT '0',
  ADD `attack_locked_until` int(11) unsigned NOT NULL DEFAULT '0';

ALTER TABLE %PREFIX%users
  ADD `collider_dm_at` int(11) unsigned NOT NULL DEFAULT '0';

CREATE TABLE %PREFIX%vouchers (
  `code` varchar(64) NOT NULL,
  `reward_metal` double(255,0) unsigned NOT NULL DEFAULT '0',
  `reward_crystal` double(255,0) unsigned NOT NULL DEFAULT '0',
  `reward_deuterium` double(255,0) unsigned NOT NULL DEFAULT '0',
  `reward_darkmatter` double(255,0) unsigned NOT NULL DEFAULT '0',
  `reward_antimatter` double(255,0) unsigned NOT NULL DEFAULT '0',
  `max_uses` int(11) unsigned NOT NULL DEFAULT '1',
  `uses` int(11) unsigned NOT NULL DEFAULT '0',
  `expires_at` int(11) unsigned NOT NULL DEFAULT '0',
  `created_at` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

UPDATE %PREFIX%config SET `trade_charge` = '50' WHERE `trade_charge` = '30';
UPDATE %PREFIX%config SET `darkmatter_cost_trader` = '250' WHERE `darkmatter_cost_trader` = '750';
UPDATE %PREFIX%config SET `gate_wait_time` = '2400' WHERE `gate_wait_time` = '3600';
