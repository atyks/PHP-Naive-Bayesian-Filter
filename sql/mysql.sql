SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------

DROP TABLE IF EXISTS `nb_categories`;
CREATE TABLE IF NOT EXISTS `nb_categories` (
  `category_id` varchar(100) NOT NULL DEFAULT '',
  `probability` double NOT NULL DEFAULT '0',
  `word_count` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------


DROP TABLE IF EXISTS `nb_references`;
CREATE TABLE IF NOT EXISTS `nb_references` (
  `id` varchar(250) NOT NULL DEFAULT '',
  `category_id` varchar(100) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

DROP TABLE IF EXISTS `nb_wordfreqs`;
CREATE TABLE IF NOT EXISTS `nb_wordfreqs` (
  `word` varchar(100) NOT NULL DEFAULT '',
  `category_id` varchar(100) NOT NULL DEFAULT '',
  `count` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`word`,`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
