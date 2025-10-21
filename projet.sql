-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Jeu 30 Mai 2024 à 22:38
-- Version du serveur: 5.5.24-log
-- Version de PHP: 5.4.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `projet`
--

-- --------------------------------------------------------

--
-- Structure de la table `personnes`
--
create database projet;
use projet;
CREATE TABLE IF NOT EXISTS `personnes` (
  `pseudo` varchar(20) NOT NULL DEFAULT '',
  `prenom` varchar(20) NOT NULL,
  `nom` varchar(20) NOT NULL,
  `age` varchar(20) NOT NULL,
  `ville` varchar(20) NOT NULL,
  PRIMARY KEY (`pseudo`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `personnes`
--

INSERT INTO `personnes` (`pseudo`, `prenom`, `nom`, `age`, `ville`) VALUES
('arn', 'Arnaud', 'Dupond', '33', 'Paris'),
('dask', 'Damien', 'Askier', '7', 'Villetaneuse'),
('email', 'Emilie', 'Ailta', '46', 'Villetaneuse'),
('jm', 'Jean', 'Martin', '20', 'Villetaneuse'),
('mdupond', 'Martin', 'Dupond', '25', 'Paris'),
('toto', 'Tom', 'Tonge', '18', 'Epinay');

--
-- Déclencheurs `personnes`
--
DROP TRIGGER IF EXISTS `check_age_before_insert`;
DELIMITER //
CREATE TRIGGER `check_age_before_insert` BEFORE INSERT ON `personnes`
 FOR EACH ROW BEGIN
    IF NEW.age <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Age must be greater than 0';
    END IF;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `check_age_before_update`;
DELIMITER //
CREATE TRIGGER `check_age_before_update` BEFORE UPDATE ON `personnes`
 FOR EACH ROW BEGIN
    IF NEW.age <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Age must be greater than 0';
    END IF;
END
//
DELIMITER ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
