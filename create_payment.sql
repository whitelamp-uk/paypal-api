
-- A persistent table in BLOTTO_MAKE_DB
CREATE TABLE IF NOT EXISTS `paypal_payment` (
  `id` INT (11) NOT NULL AUTO_INCREMENT,
  `txn_ref` varchar(255) CHARACTER SET ascii DEFAULT NULL,
  `callback_at` datetime DEFAULT NULL,
  `refno` bigint(20) unsigned DEFAULT NULL,
  `cref` varchar(255) CHARACTER SET ascii DEFAULT NULL,
  `quantity` tinyint(3) unsigned NOT NULL,
  `draws` tinyint(3) unsigned NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `dob` date NOT NULL,
  `email` varchar(255) CHARACTER SET ascii NOT NULL,
  `mobile` varchar(255) CHARACTER SET ascii NOT NULL,
  `telephone` varchar(255) CHARACTER SET ascii NOT NULL,
  `postcode` varchar(255) CHARACTER SET ascii NOT NULL,
  `address_1` varchar(255) NOT NULL,
  `address_2` varchar(255) NOT NULL,
  `address_3` varchar(255) NOT NULL,
  `town` varchar(255) NOT NULL,
  `county` varchar(255) NOT NULL,
  `gdpr` tinyint(1) unsigned NOT NULL,
  `terms` tinyint(1) unsigned NOT NULL,
  `pref_email` varchar(255) NOT NULL,
  `pref_sms` varchar(255) NOT NULL,
  `pref_post` varchar(255) NOT NULL,
  `pref_phone` varchar(255) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `txn_ref` (`txn_ref`),
  KEY `created` (`created`),
  KEY `paid` (`paid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
;

