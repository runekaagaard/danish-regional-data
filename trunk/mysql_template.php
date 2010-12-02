SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `dk`
--

-- --------------------------------------------------------

--
-- Table structure for table `dk_commune`
--

CREATE TABLE IF NOT EXISTS `dk_commune` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(18) COLLATE utf8_danish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;

--
-- Dumping data for table `dk_commune`
--
<?=array_to_sql('dk_commune', $communes)?>

-- --------------------------------------------------------

--
-- Table structure for table `dk_commune_regions`
--

CREATE TABLE IF NOT EXISTS `dk_commune_regions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `commune_id` int(11) NOT NULL,
  `region_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `dk_commune_regions`
--

<?=array_to_sql('dk_commune_regions', $commune_regions)?>

-- --------------------------------------------------------

--
-- Table structure for table `dk_postalcode`
--

CREATE TABLE IF NOT EXISTS `dk_postalcode` (
  `id` smallint(11) unsigned NOT NULL AUTO_INCREMENT,
  `commune_id` int(11) NOT NULL,
  `name` varchar(18) COLLATE utf8_danish_ci NOT NULL,
  `commune_ids` text COLLATE utf8_danish_ci NOT NULL,
  `commune_names` text COLLATE utf8_danish_ci NOT NULL,
  `region_ids` text COLLATE utf8_danish_ci NOT NULL,
  `region_names` text COLLATE utf8_danish_ci NOT NULL,
  `lat` float NOT NULL,
  `lng` float NOT NULL,
  `within_5_km` text COLLATE utf8_danish_ci NOT NULL,
  `within_10_km` text COLLATE utf8_danish_ci NOT NULL,
  `within_25_km` text COLLATE utf8_danish_ci NOT NULL,
  `within_50_km` text COLLATE utf8_danish_ci NOT NULL,
  `within_100_km` text COLLATE utf8_danish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci AUTO_INCREMENT=9991 ;

<?=array_to_sql('dk_postalcode', $postalcodes)?>

-- --------------------------------------------------------

--
-- Table structure for table `dk_postalcode_communes`
--

CREATE TABLE IF NOT EXISTS `dk_postalcode_communes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `postalcode_id` int(11) NOT NULL,
  `commune_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `dk_postalcode_communes`
--
<?=array_to_sql('dk_postalcode_communes', $postalcode_communes)?>

-- --------------------------------------------------------

--
-- Table structure for table `dk_region`
--

CREATE TABLE IF NOT EXISTS `dk_region` (
  `id` smallint(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(11) COLLATE utf8_danish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci AUTO_INCREMENT=1086 ;

--
-- Dumping data for table `dk_region`
--
<?=array_to_sql('dk_region', $regions)?>
