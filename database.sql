/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Affichage de la table hrpg
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hrpg`;

CREATE TABLE `hrpg` (
  `id` int(15) unsigned NOT NULL AUTO_INCREMENT,
  `nom` text NOT NULL,
  `mdp` text NOT NULL,
  `hf` tinyint(1) NOT NULL,
  `str` tinyint(1) NOT NULL,
  `mind` tinyint(1) NOT NULL,
  `hp` tinyint(1) NOT NULL,
  `leader` tinyint(1) NOT NULL DEFAULT '0',
  `traitre` tinyint(1) NOT NULL DEFAULT '0',
  `vote` tinyint(1) NOT NULL DEFAULT '0',
  `tag1` text NOT NULL,
  `tag2` text NOT NULL,
  `tag3` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Affichage de la table loot
# ------------------------------------------------------------

DROP TABLE IF EXISTS `loot`;

CREATE TABLE `loot` (
  `id` int(15) unsigned NOT NULL AUTO_INCREMENT,
  `idh` int(15) NOT NULL,
  `quoi` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Affichage de la table sondage
# ------------------------------------------------------------

DROP TABLE IF EXISTS `sondage`;

CREATE TABLE `sondage` (
  `choix` text NOT NULL,
  `c1` text NOT NULL,
  `c2` text NOT NULL,
  `c3` text NOT NULL,
  `c4` text NOT NULL,
  `c5` text NOT NULL,
  `c6` text NOT NULL,
  `c7` text NOT NULL,
  `c8` text NOT NULL,
  `c9` text NOT NULL,
  `c10` text NOT NULL,
  `choixtag` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `sondage` WRITE;
/*!40000 ALTER TABLE `sondage` DISABLE KEYS */;

INSERT INTO `sondage` (`choix`, `c1`, `c2`, `c3`, `c4`, `c5`, `c6`, `c7`, `c8`, `c9`, `c10`, `choixtag`)
VALUES
	('','','','','','','','','','','','A');

/*!40000 ALTER TABLE `sondage` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
