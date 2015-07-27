CREATE TABLE  `smvc_sessions` (
  `session_id` varchar(40) NOT NULL,
  `previous_id` varchar(40) NOT NULL,
  `user_agent` text NOT NULL,
  `ip_hash` char(32) NOT NULL DEFAULT '',
  `created` int(10) unsigned NOT NULL DEFAULT '0',
  `updated` int(10) unsigned NOT NULL DEFAULT '0',
  `payload` longtext NOT NULL,
  PRIMARY KEY (`session_id`),
  UNIQUE KEY `PREVIOUS` (`previous_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;