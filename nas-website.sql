-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 15. Feb 2025 um 15:03
-- Server-Version: 10.4.32-MariaDB
-- PHP-Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `nas-website`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `accounts`
--

CREATE TABLE `accounts` (
  `USERNAME` varchar(255) DEFAULT NULL,
  `PASSWORD` varchar(255) DEFAULT NULL,
  `EMAIL` varchar(255) DEFAULT NULL,
  `reset_code` varchar(6) DEFAULT NULL,
  `rememberTOKEN` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Daten für Tabelle `accounts`
--

INSERT INTO `accounts` (`USERNAME`, `PASSWORD`, `EMAIL`, `reset_code`, `rememberTOKEN`) VALUES
('Marcus', '$2y$10$6DNV5z4dwUNtx7YVGJ1dK.LD86IcwBnxmkuAQ9lesxL3GzEHgjWVe', 'marcusnebel09@gmail.com', NULL, '299b7e5163024bb9b2b8309453d189a84d23525c4b406a74aabd2635c22e7e70'),
('Philipp', '$2y$10$XfQJYWqIlAS90WFW53Vu.OggJYN5ijuGqajTeRvDTc77A1Tc4Qee6', 'philijueppner1301@gmail.com', NULL, 'b16481034c0f6296d79183d5aba11e3640bc248ed7ccbf9d520fe17cff3db77d');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `accounts`
--
ALTER TABLE `accounts`
  ADD UNIQUE KEY `USERNAME` (`USERNAME`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
