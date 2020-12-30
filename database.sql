-- --------------------------------------------------------

--
-- Structure de la table `hrpg`
--

CREATE TABLE `hrpg` (
  `id` int(15) NOT NULL,
  `nom` text NOT NULL,
  `mdp` text NOT NULL,
  `hf` tinyint(1) NOT NULL,
  `str` tinyint(1) NOT NULL,
  `mind` tinyint(1) NOT NULL,
  `hp` tinyint(1) NOT NULL,
  `leader` tinyint(1) NOT NULL DEFAULT '0',
  `traitre` tinyint(1) NOT NULL,
  `vote` tinyint(1) NOT NULL DEFAULT '0',
  `tag1` text NOT NULL,
  `tag2` text NOT NULL,
  `tag3` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `loot`
--

CREATE TABLE `loot` (
  `id` int(15) NOT NULL,
  `idh` int(15) NOT NULL,
  `quoi` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



-- --------------------------------------------------------

--
-- Structure de la table `sondage`
--

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
