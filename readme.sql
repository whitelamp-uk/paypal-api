
CREATE TABLE IF NOT EXISTS `_readme` (
  `project` char(64),
  `location` varchar(255) NOT NULL,
  PRIMARY KEY (`project`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

INSERT IGNORE INTO `_readme` (`project`, `location`) VALUES
('whitelamp-uk/paypal-api', 'https://github.com/whitelamp-uk/paypal-api.git');
