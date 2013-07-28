/*******************************************************************************************
*    Copyright © 2012-2013 Michael Felger                                                  *
*                                                                                          *
*    This file is part of EOSys.                                                           *
*                                                                                          *
*    EOSys is free software: you can redistribute it and/or modify                         *
*    it under the terms of the GNU General Public License Version 3 as published by        *
*    the Free Software Foundation.                                                         *
*                                                                                          *
*    This program is distributed in the hope that it will be useful,                       *
*    but WITHOUT ANY WARRANTY; without even the implied warranty of                        *
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                         *
*    GNU General Public License Version 3 for more details.                                *
*                                                                                          *
*    You should have received a copy of the GNU General Public License Version 3           *
*    along with EOSys.  If not, see <http://www.gnu.org/licenses/gpl-3.0/>.                *
*                                                                                          *
*    Siehe ./gpl-3.0.txt (GNU GENERAL PUBLIC LICENSE Version 3)                            *
********************************************************************************************/

-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 27. Dez 2012 um 21:30
-- Server Version: 5.5.16
-- PHP-Version: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `eosys`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `abijahrgang`
--

CREATE TABLE IF NOT EXISTS `abijahrgang` (
  `aid` int(11) NOT NULL AUTO_INCREMENT,
  `aname` int(11) DEFAULT NULL,
  PRIMARY KEY (`aid`),
  UNIQUE KEY `aname` (`aname`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `besucht`
--

CREATE TABLE IF NOT EXISTS `besucht` (
  `sid` int(11) NOT NULL,
  `kid` int(11) NOT NULL,
  PRIMARY KEY (`sid`,`kid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fehlzeit`
--

CREATE TABLE IF NOT EXISTS `fehlzeit` (
  `fid` int(11) NOT NULL AUTO_INCREMENT,
  `utype` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `ffehldatum` date NOT NULL,
  `fgrund` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `fsid` int(11) NOT NULL,
  `feintragedatum` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `faktualisiertdatum` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `faktualisiertvonutype` int(11) NOT NULL,
  `faktualisiertvonuserid` int(11) NOT NULL,
  `fversion` int(11) NOT NULL,
  PRIMARY KEY (`fid`,`fversion`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fehlzeitenkurse`
--

CREATE TABLE IF NOT EXISTS `fehlzeitenkurse` (
  `fid` int(11) NOT NULL,
  `kid` int(11) NOT NULL,
  `stunde` int(11) NOT NULL,
  PRIMARY KEY (`fid`,`kid`,`stunde`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fehlzeitenschueler`
--

CREATE TABLE IF NOT EXISTS `fehlzeitenschueler` (
  `fid` int(11) NOT NULL,
  `sid` int(11) NOT NULL,
  UNIQUE KEY `fid` (`fid`,`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fehlzeitstatus`
--

CREATE TABLE IF NOT EXISTS `fehlzeitstatus` (
  `fsid` int(11) NOT NULL AUTO_INCREMENT,
  `fstyp` int(11) NOT NULL,
  `fsname` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`fsid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kurs`
--

CREATE TABLE IF NOT EXISTS `kurs` (
  `kid` int(11) NOT NULL AUTO_INCREMENT,
  `ktid` int(11) NOT NULL,
  `knummer` int(11) DEFAULT NULL,
  `aid` int(11) NOT NULL,
  `kversion` int(11) DEFAULT NULL,
  `lid` int(11) DEFAULT NULL,
  PRIMARY KEY (`kid`),
  UNIQUE KEY `kid` (`ktid`,`knummer`,`aid`,`kversion`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kurstypen`
--

CREATE TABLE IF NOT EXISTS `kurstypen` (
  `ktid` int(11) NOT NULL AUTO_INCREMENT,
  `ktname` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `ktkuerzel` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `ktstunden` int(11) DEFAULT NULL,
  PRIMARY KEY (`ktid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------


--
-- Tabellenstruktur für Tabelle `lehrer`
--

CREATE TABLE IF NOT EXISTS `lehrer` (
  `lid` int(11) NOT NULL AUTO_INCREMENT,
  `limportedid` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `lkuerzel` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lname` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `lvorname` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `lemail` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `lsendemail` int(11) DEFAULT NULL,
  `lsendemailtutor` int(11) DEFAULT NULL,
  PRIMARY KEY (`lid`),
  UNIQUE KEY `limportedid` (`limportedid`),
  UNIQUE KEY `lkuerzel` (`lkuerzel`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `logins`
--

CREATE TABLE IF NOT EXISTS `logins` (
  `logid` int(11) NOT NULL AUTO_INCREMENT,
  `userid` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `usertype` int(11) NOT NULL,
  `random` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `zeit` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`logid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `rechte`
--

CREATE TABLE IF NOT EXISTS `rechte` (
  `rid` int(11) NOT NULL AUTO_INCREMENT,
  `rname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`rid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `schueler`
--

CREATE TABLE IF NOT EXISTS `schueler` (
  `sid` int(50) NOT NULL AUTO_INCREMENT,
  `simportedid` varchar(50) NOT NULL,
  `sname` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `svorname` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `semail` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `aid` int(50) NOT NULL,
  `lid` int(50) NOT NULL,
  `IDNR` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `sendemail` tinyint(1) DEFAULT '0',
  `sgeburtsdatum` date NOT NULL,
  `sstatus` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`sid`),
  UNIQUE KEY `simportedid` (`simportedid`,`aid`),
  UNIQUE KEY `IDNR` (`IDNR`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `stunden`
--

CREATE TABLE IF NOT EXISTS `stunden` (
  `stunde` int(2) NOT NULL AUTO_INCREMENT,
  `stname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`stunde`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `userrechte`
--

CREATE TABLE IF NOT EXISTS `userrechte` (
  `lid` int(11) NOT NULL,
  `rid` int(11) NOT NULL,
  PRIMARY KEY (`lid`,`rid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `winprosakurstypen`
--

CREATE TABLE IF NOT EXISTS `winprosakurstypen` (
  `ktid` int(11) NOT NULL,
  `seminar1` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `seminar2` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `seminar3` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `seminar4` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `kursnummerseminar1` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `kursnummerseminar2` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `kursnummerseminar3` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `kursnummerseminar4` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `reli` text COLLATE utf8_unicode_ci,
  `aktiv` int(11) NOT NULL,
  PRIMARY KEY (`ktid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

/* Initialdaten einfügen */

INSERT INTO `stunden` (`stunde`, `stname`) VALUES
(1, '7:30 - 8:15'),
(2, '8:20 - 9:05'),
(3, '9:10 - 9:55'),
(4, '10:10 - 10:55'),
(5, '11:00 - 11:45'),
(6, '11:50 - 12:35'),
(7, '14:00 - 14:45'),
(8, '14:50 - 15:35'),
(9, '15:45 - 16:30'),
(10, '16.35 - 17.20');

INSERT INTO `lehrer` (`lid`, `limportedid`, `lkuerzel`, `lname`, `lvorname`, `lemail`, `lsendemail`, `lsendemailtutor`) VALUES (1, '', NULL, '', '', NULL, 0, 0);

INSERT INTO `kurs` (`kid`, `ktid`, `knummer`, `aid`, `kversion`, `lid`) VALUES (1, 1, NULL, 1, 0, 1);

INSERT INTO `fehlzeitstatus` (`fsid`, `fstyp`, `fsname`) VALUES
(1, 1, 'warte auf Bestätigung'),
(2, 2, 'Entschuldigung nicht akzeptiert'),
(3, 3, 'Entschuldigt'),
(4, 4, 'Entschuldigung liegt nicht vor'),
(5, 1, 'Beurlaubung beantragen'),
(6, 3, 'Beurlaubt'),
(7, 5, 'Gelöscht'),
(8, 3, 'Entschuldigt mit Attest'),
(9, 3, 'Entschuldigt durch Fachlehrer'),
(10, 5, 'Gelöscht von System');

INSERT INTO `abijahrgang` (`aid`, `aname`) VALUES
(1, NULL);

INSERT INTO `rechte` (`rid`, `rname`) VALUES
(1, 'Adminfunktionen anzeigen'),
(2, 'alle Fehlzeiten ansehen'),
(3, 'Lehrer bearbeiten'),
(4, 'Schüler bearbeiten'),
(5, 'Kurse bearbeiten'),
(6, 'Alle Fehlzeiten löschen');

INSERT INTO `kurstypen` (`ktid`, `ktname`, `ktkuerzel`, `ktstunden`) VALUES
(1, '(*) Alle Kurse', '', NULL),
(2, 'Mathematik', 'M', 4),
(3, 'Deutsch', 'D', 4),
(4, 'Englisch', 'E', 4),
(5, 'Französisch', 'F', 4),
(6, 'Latein', 'L', 4),
(7, 'Bildende Kunst', 'BK', 4),
(8, 'Bildende Kunst', 'bk', 2),
(9, 'Musik', 'Mu', 4),
(10, 'Musik', 'mu', 2),
(11, 'Geschichte', 'G', 4),
(12, 'Geschichte', 'g', 2),
(13, 'Gemeinschaftskunde', 'GK', 4),
(14, 'Gemeinschaftskunde', 'gk', 2),
(15, 'Geographie', 'EK', 4),
(16, 'Geographie', 'ek', 2),
(17, 'Wirtschaft', 'Wi', 4),
(18, 'Ev.Religion', 'EvR', 4),
(19, 'Ev.Religion', 'evR', 2),
(20, 'Kath. Religion', 'KaR', 4),
(21, 'Kath. Religion', 'kaR', 2),
(22, 'Ethik', 'Eth', 4),
(23, 'Ethik', 'eth', 2),
(24, 'Physik', 'Ph', 4),
(25, 'Physik', 'ph', 2),
(26, 'Chemie', 'Ch', 4),
(27, 'Chemie', 'ch', 2),
(28, 'Biologie', 'Bio', 4),
(29, 'Biologie', 'bio', 2),
(30, 'Sport', 'Sp', 4),
(31, 'Sport', 'sp', 2),
(32, 'Besondere Lernleistung', 'Sem', 4),
(33, 'Atronomie', 'ast', 2),
(34, 'Darstellende Geometrie', 'dg', 2),
(35, 'Geologie', 'geo', 2),
(36, 'Problemlösen mit CAS', 'cas', 2),
(37, 'Informatik', 'inf', 2),
(38, 'Literatur', 'lit', 2),
(39, 'Philosophie', 'phi', 2),
(40, 'Psychologie', 'psy', 2),
(41, 'Italienisch', 'I', 4),
(42, 'Literatur und Theater', 'lth', 2);


INSERT INTO `winprosakurstypen` (`ktid`, `seminar1`, `seminar2`, `seminar3`, `seminar4`, `kursnummerseminar1`, `kursnummerseminar2`, `kursnummerseminar3`, `kursnummerseminar4`, `reli`, `aktiv`) VALUES
(2, 'Sem1-D', 'Sem2-D', 'Sem3-D', 'Sem4-D', 'PKSem1-D', 'PKSem2-D', 'PKSem3-D', 'PKSem4-D', NULL, 1),
(3, 'Sem1-E', 'Sem2-E', 'Sem3-E', 'Sem4-E', 'PKSem1-E', 'PKSem2-E', 'PKSem3-E', 'PKSem4-E', NULL, 1),
(4, 'Sem1-F', 'Sem2-F', 'Sem3-F', 'Sem4-F', 'PKSem1-F', 'PKSem2-F', 'PKSem3-F', 'PKSem4-F', NULL, 1),
(40, 'Sem1-I', 'Sem2-I', 'Sem3-I', 'Sem4-I', 'PKSem1-I', 'PKSem2-I', 'PKSem3-I', 'PKSem4-I', NULL, 1),
(6, 'Sem1-BK', 'Sem2-BK', 'Sem3-BK', 'Sem4-BK', 'PKSem1-BK', 'PKSem2-BK', 'PKSem3-BK', 'PKSem4-BK', NULL, 1),
(7, 'Sem1-BK', 'Sem2-BK', 'Sem3-BK', 'Sem4-BK', 'PKSem1-BK', 'PKSem2-BK', 'PKSem3-BK', 'PKSem4-BK', NULL, 1),
(8, 'Sem1-Mu', 'Sem2-Mu', 'Sem3-Mu', 'Sem4-Mu', 'PKSem1-Mu', 'PKSem2-Mu', 'PKSem3-Mu', 'PKSem4-Mu', NULL, 1),
(9, 'Sem1-Mu', 'Sem2-Mu', 'Sem3-Mu', 'Sem4-Mu', 'PKSem1-Mu', 'PKSem2-Mu', 'PKSem3-Mu', 'PKSem4-Mu', NULL, 1),
(10, 'Sem1-G', 'Sem2-G', 'Sem3-G', 'Sem4-G', 'PKSem1-G', 'PKSem2-G', 'PKSem3-G', 'PKSem4-G', NULL, 1),
(11, 'Sem1-G', 'Sem2-G', 'Sem3-G', 'Sem4-G', 'PKSem1-G', 'PKSem2-G', 'PKSem3-G', 'PKSem4-G', NULL, 1),
(14, 'Sem1-Geogr', 'Sem2-Geogr', 'Sem3-Geogr', 'Sem4-Geogr', 'PKSem1-Geogr', 'PKSem2-Geogr', 'PKSem3-Geogr', 'PKSem4-Geogr', NULL, 1),
(15, 'Sem1-Geogr', 'Sem2-Geogr', 'Sem3-Geogr', 'Sem4-Geogr', 'PKSem1-Geogr', 'PKSem2-Geogr', 'PKSem3-Geogr', 'PKSem4-Geogr', NULL, 1),
(12, 'Sem1-Gk', 'Sem2-Gk', 'Sem3-Gk', 'Sem4-Gk', 'PKSem1-Gk', 'PKSem2-Gk', 'PKSem3-Gk', 'PKSem4-Gk', NULL, 1),
(13, 'Sem1-Gk', 'Sem2-Gk', 'Sem3-Gk', 'Sem4-Gk', 'PKSem1-Gk', 'PKSem2-Gk', 'PKSem3-Gk', 'PKSem4-Gk', NULL, 1),
(16, 'Sem1-Wi', 'Sem2-Wi', 'Sem3-Wi', 'Sem4-Wi', 'PKSem1-Wi', 'PKSem2-Wi', 'PKSem3-Wi', 'PKSem4-Wi', NULL, 1),
(1, 'Sem1-M', 'Sem2-M', 'Sem3-M', 'Sem4-M', 'PKSem1-M', 'PKSem2-M', 'PKSem3-M', 'PKSem4-M', NULL, 1),
(23, 'Sem1-Ph', 'Sem2-Ph', 'Sem3-Ph', 'Sem4-Ph', 'PKSem1-Ph', 'PKSem2-Ph', 'PKSem3-Ph', 'PKSem4-Ph', NULL, 1),
(24, 'Sem1-Ph', 'Sem2-Ph', 'Sem3-Ph', 'Sem4-Ph', 'PKSem1-Ph', 'PKSem2-Ph', 'PKSem3-Ph', 'PKSem4-Ph', NULL, 1),
(25, 'Sem1-Ch', 'Sem2-Ch', 'Sem3-Ch', 'Sem4-Ch', 'PKSem1-Ch', 'PKSem2-Ch', 'PKSem3-Ch', 'PKSem4-Ch', NULL, 1),
(26, 'Sem1-Ch', 'Sem2-Ch', 'Sem3-Ch', 'Sem4-Ch', 'PKSem1-Ch', 'PKSem2-Ch', 'PKSem3-Ch', 'PKSem4-Ch', NULL, 1),
(27, 'Sem1-Bio', 'Sem2-Bio', 'Sem3-Bio', 'Sem4-Bio', 'PKSem1-Bio', 'PKSem2-Bio', 'PKSem3-Bio', 'PKSem4-Bio', NULL, 1),
(28, 'Sem1-Bio', 'Sem2-Bio', 'Sem3-Bio', 'Sem4-Bio', 'PKSem1-Bio', 'PKSem2-Bio', 'PKSem3-Bio', 'PKSem4-Bio', NULL, 1),
(29, 'Sem1-S', 'Sem2-S', 'Sem3-S', 'Sem4-S', 'PKSem1-S', 'PKSem2-S', 'PKSem3-S', 'PKSem4-S', NULL, 1),
(30, 'Sem1-S', 'Sem2-S', 'Sem3-S', 'Sem4-S', 'PKSem1-S', 'PKSem2-S', 'PKSem3-S', 'PKSem4-S', NULL, 1),
(31, 'Sem1-SF', 'Sem2-SF', 'Sem3-SF', 'Sem4-SF', 'PKSem1-SF', 'PKSem2-SF', 'PKSem3-SF', 'PKSem4-SF', NULL, 1),
(39, 'Sem1-Psy', 'Sem2-Psy', 'Sem3-Psy', 'Sem4-Psy', 'PKSem1-Psy', 'PKSem2-Psy', 'PKSem3-Psy', 'PKSem4-Psy', NULL, 1),
(37, 'Sem1-LTh', 'Sem2-LTh', 'Sem3-LTh', 'Sem4-LTh', 'PKSem1-LTh', 'PKSem2-LTh', 'PKSem3-LTh', 'PKSem4-LTh', NULL, 1),
(36, 'Sem1-Inf', 'Sem2-Inf', 'Sem3-Inf', 'Sem4-Inf', 'PKSem1-Inf', 'PKSem2-Inf', 'PKSem3-Inf', 'PKSem4-Inf', NULL, 1),
(38, 'Sem1-Phi', 'Sem2-Phi', 'Sem3-Phi', 'Sem4-Phi', 'PKSem1-Phi', 'PKSem2-Phi', 'PKSem3-Phi', 'PKSem4-Phi', NULL, 1),
(35, 'Sem1-CAS', 'Sem2-CAS', 'Sem3-CAS', 'Sem4-CAS', 'PKSem1-CAS', 'PKSem2-CAS', 'PKSem3-CAS', 'PKSem4-CAS', NULL, 0),
(33, 'Sem1-DG', 'Sem2-DG', 'Sem3-DG', 'Sem4-DG', 'PKSem1-DG', 'PKSem2-DG', 'PKSem3-DG', 'PKSem4-DG', NULL, 1),
(5, 'Sem1-L', 'Sem2-L', 'Sem3-L', 'Sem4-L', 'PKSem1-L', 'PKSem2-L', 'PKSem3-L', 'PKSem4-L', NULL, 1),
(34, 'Sem1-Geol', 'Sem2-Geol', 'Sem3-Geol', 'Sem4-Geol', 'PKSem1-Geol', 'PKSem2-Geol', 'PKSem3-Geol', 'PKSem4-Geol', NULL, 1),
(17, 'Sem1-Rel', 'Sem2-Rel', 'Sem3-Rel', 'Sem4-Rel', 'PKSem1-Rel', 'PKSem2-Rel', 'PKSem3-Rel', 'PKSem4-Rel', 'evR', 1),
(18, 'Sem1-Rel', 'Sem2-Rel', 'Sem3-Rel', 'Sem4-Rel', 'PKSem1-Rel', 'PKSem2-Rel', 'PKSem3-Rel', 'PKSem4-Rel', 'evR', 1),
(19, 'Sem1-Rel', 'Sem2-Rel', 'Sem3-Rel', 'Sem4-Rel', 'PKSem1-Rel', 'PKSem2-Rel', 'PKSem3-Rel', 'PKSem4-Rel', 'kR', 1),
(20, 'Sem1-Rel', 'Sem2-Rel', 'Sem3-Rel', 'Sem4-Rel', 'PKSem1-Rel', 'PKSem2-Rel', 'PKSem3-Rel', 'PKSem4-Rel', 'kR', 1),
(21, 'Sem1-Eth', 'Sem2-Eth', 'Sem3-Eth', 'Sem4-Eth', 'PKSem1-Eth', 'PKSem2-Eth', 'PKSem3-Eth', 'PKSem4-Eth', 'Eth', 1),
(22, 'Sem1-Eth', 'Sem2-Eth', 'Sem3-Eth', 'Sem4-Eth', 'PKSem1-Eth', 'PKSem2-Eth', 'PKSem3-Eth', 'PKSem4-Eth', 'Eth', 1),
(41, 'Sem1-LTh', 'Sem2-LTh', 'Sem3-LTh', 'Sem4-LTh', 'PKSem1-LTh', 'PKSem2-LTh', 'PKSem2-LTh', 'PKSem2-LTh', NULL, 1);