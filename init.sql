CREATE TABLE IF NOT EXISTS `accounts` (
  `USERNAME` varchar(255) DEFAULT NULL,
  `PASSWORD` varchar(255) DEFAULT NULL,
  `EMAIL` varchar(255) DEFAULT NULL,
  `reset_code` varchar(6) DEFAULT NULL,
  `rememberTOKEN` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

ALTER TABLE `accounts`
  ADD UNIQUE KEY `USERNAME` (`USERNAME`);

COMMIT;

ALTER USER 'root'@'%' IDENTIFIED WITH mysql_native_password BY '59LWrt!mDo6GC4';
FLUSH PRIVILEGES;
