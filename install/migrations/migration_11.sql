ALTER TABLE %PREFIX%messages
  ADD `message_archived` tinyint(1) unsigned NOT NULL DEFAULT '0' AFTER `message_deleted`;

ALTER TABLE %PREFIX%messages
  ADD INDEX `message_archived` (`message_owner`, `message_archived`);
