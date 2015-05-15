SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

-- --------------------------------------------------------

--
-- Table Structure `qa_mentions`
--

CREATE TABLE IF NOT EXISTS `qa_mentions` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `to_id` int(11) NOT NULL,
  `from_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `post_type` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `eliminated` tinyint(1) NOT NULL DEFAULT '0',
  `closed` tinyint(1) NOT NULL DEFAULT '0',
  `date` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Table Structure `qa_userlogs`
--

CREATE TABLE IF NOT EXISTS `qa_userlogs` (
  `user_id` int(11) NOT NULL,
  `name` varchar(40) NOT NULL,
  `value` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
