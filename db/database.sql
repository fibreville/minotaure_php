# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Affichage de la table character_tag
# ------------------------------------------------------------

DROP TABLE IF EXISTS `character_tag`;

CREATE TABLE `character_tag` (
  `id_player` int(11) unsigned NOT NULL,
  `id_tag` int(11) NOT NULL,
  PRIMARY KEY (`id_player`,`id_tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



# Affichage de la table hrpg
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hrpg`;

CREATE TABLE `hrpg` (
  `id` int(15) unsigned NOT NULL AUTO_INCREMENT,
  `nom` mediumtext NOT NULL,
  `mdp` mediumtext NOT NULL,
  `carac3` tinyint(1) NOT NULL,
  `carac2` tinyint(1) NOT NULL,
  `carac1` tinyint(1) NOT NULL,
  `hp` tinyint(1) NOT NULL,
  `wp` tinyint(1) NOT NULL,
  `leader` tinyint(1) NOT NULL DEFAULT '0',
  `traitre` tinyint(1) NOT NULL DEFAULT '0',
  `vote` tinyint(1) NOT NULL DEFAULT '0',
  `log` mediumtext,
  `lastlog` text,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



# Affichage de la table loot
# ------------------------------------------------------------

DROP TABLE IF EXISTS `loot`;

CREATE TABLE `loot` (
  `id` int(15) unsigned NOT NULL AUTO_INCREMENT,
  `idh` int(15) NOT NULL,
  `quoi` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



# Affichage de la table sondage
# ------------------------------------------------------------

DROP TABLE IF EXISTS `sondage`;

CREATE TABLE `sondage` (
  `choix` mediumtext NOT NULL,
  `c1` mediumtext NOT NULL,
  `c2` mediumtext NOT NULL,
  `c3` mediumtext NOT NULL,
  `c4` mediumtext NOT NULL,
  `c5` mediumtext NOT NULL,
  `c6` mediumtext NOT NULL,
  `c7` mediumtext NOT NULL,
  `c8` mediumtext NOT NULL,
  `c9` mediumtext NOT NULL,
  `c10` mediumtext NOT NULL,
  `choixtag` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

LOCK TABLES `sondage` WRITE;
/*!40000 ALTER TABLE `sondage` DISABLE KEYS */;

INSERT INTO `sondage` (`choix`, `c1`, `c2`, `c3`, `c4`, `c5`, `c6`, `c7`, `c8`, `c9`, `c10`, `choixtag`)
VALUES
	('','','','','','','','','','','','');

/*!40000 ALTER TABLE `sondage` ENABLE KEYS */;
UNLOCK TABLES;


# Affichage de la table tag
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tag`;

CREATE TABLE `tag` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(11) NOT NULL DEFAULT '',
  `category` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
