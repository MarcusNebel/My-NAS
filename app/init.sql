-- Wähle die richtige Datenbank
USE nas-website;

-- Erstelle die Tabelle, falls sie nicht existiert
CREATE TABLE IF NOT EXISTS `accounts` (
  `ID` INT AUTO_INCREMENT PRIMARY KEY,
  `USERNAME` VARCHAR(255) DEFAULT NULL,
  `PASSWORD` VARCHAR(255) DEFAULT NULL,
  `EMAIL` VARCHAR(255) DEFAULT NULL,
  `reset_code` VARCHAR(6) DEFAULT NULL,
  `chat_user_id` VARCHAR(6) DEFAULT NULL,
  `rememberTOKEN` VARCHAR(64) DEFAULT NULL,
  `api_key` VARCHAR(64) DEFAULT NULL,
  `server_rank` ENUM('Admin', 'User', 'Moderator') NOT NULL DEFAULT 'User'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender VARCHAR(6) NOT NULL,
    receiver VARCHAR(6),
    group_id INT,
    message TEXT,
    attachment_path VARCHAR(255),
    status ENUM('sent', 'delivered', 'read') DEFAULT 'sent',
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE chat_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE group_members (
    group_id INT,
    username VARCHAR(255),
    PRIMARY KEY (group_id, username),
    FOREIGN KEY (group_id) REFERENCES chat_groups(id) ON DELETE CASCADE
);

CREATE TABLE contacts (
    owner_id VARCHAR(6) NOT NULL,
    contact_id VARCHAR(6) NOT NULL,
    PRIMARY KEY (owner_id, contact_id)
);

-- Setze das Root-Passwort auf mysql_native_password, falls erforderlich
ALTER USER 'root'@'%' IDENTIFIED BY '59LWrt!mDo6GC4';
FLUSH PRIVILEGES;
