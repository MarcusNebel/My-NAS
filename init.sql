-- Wähle die richtige Datenbank
USE nas-website;

-- Erstelle die Tabelle, falls sie nicht existiert
CREATE TABLE IF NOT EXISTS `accounts` (
  `ID` INT AUTO_INCREMENT PRIMARY KEY,
  `USERNAME` VARCHAR(255) DEFAULT NULL,
  `PASSWORD` VARCHAR(255) DEFAULT NULL,
  `EMAIL` VARCHAR(255) DEFAULT NULL,
  `reset_code` VARCHAR(6) DEFAULT NULL,
  `rememberTOKEN` VARCHAR(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Setze das Root-Passwort auf mysql_native_password, falls erforderlich
ALTER USER 'root'@'%' IDENTIFIED WITH mysql_native_password BY '59LWrt!mDo6GC4';
FLUSH PRIVILEGES;
