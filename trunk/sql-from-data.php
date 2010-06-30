<?php
// Errors on.
error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', 1);

// Settings, change these if you want.
define('DRD_ROOT', realpath(dirname(__FILE__)) . '/');
define('DRD_FILE_IN', DRD_ROOT . 'danish_regional_data.txt');
define('DRD_FILE_OUT', DRD_ROOT . 'danish_regional_data.sql');
define('DRD_TABLE_PREFIX', 'danish_regional_data_');
define('DRD_DENORMALIZE_CITY', FALSE);

// Includes.
require DRD_ROOT . 'lib.php';

// Variables.
$data_as_text = file_get_contents(DRD_FILE_IN);
$regions = array();
$communes = array();
$cities = array();

// Process data.
$max_region_name_len = 0;
$max_commune_name_len = 0;
$max_city_name_len = 0;
foreach(explode("\n", $data_as_text) as $row) {
    if (empty($row)) continue;
    $row = trim($row);
    $s = "[\ ]+";
    $n = "[0-9]+";
    $nn = "[^0-9]+";
    if (0 === preg_match("/(?P<region_id>$n){$s}Region{$s}(?P<region_name>$nn)(?P<commune_id>$n)(?P<commune_name>.*){$nn}(?P<city_id>$n)(?P<city_name>.*)/u", $row, $ms)) {
        var_dump($row);
        die("Invalid file\n");
    }
    $ms['commune_name'] = str_ireplace("kommune", '', $ms['commune_name']);
    foreach ($ms as &$m) $m = trim($m);
    $max_region_name_len = max($max_region_name_len, strlen($ms['region_name']));
    $max_commune_name_len = max($max_commune_name_len, strlen($ms['commune_name']));
    $max_city_name_len = max($max_city_name_len, strlen($ms['city_name']));
    $regions[$ms['region_id']] = array('id' => $ms['region_id'] ,'name' => $ms['region_name']);
    $communes[$ms['commune_id']] = array('id' => $ms['commune_id'], 'region_id' => $ms['region_id'], 'name' => $ms['commune_name']);
    $cities[$ms['city_id']] = array('id' => $ms['city_id'], 'commune_id' => $ms['commune_id'], 'name' => $ms['city_name']);
    if (DRD_DENORMALIZE_CITY) {
        $cities[$ms['city_id']]['region_name'] = $ms['region_name'];
        $cities[$ms['city_id']]['commune_name'] = $ms['commune_name'];
    }
}

// Collect sql.
$pf = DRD_TABLE_PREFIX;
$sql_city_denormalized = DRD_DENORMALIZE_CITY ?
    "
  `region_name` varchar(11) COLLATE utf8_danish_ci NOT NULL,
  `commune_name` varchar(18) COLLATE utf8_danish_ci NOT NULL,"
  : '';

$sql = 
"CREATE TABLE IF NOT EXISTS `{$pf}region` (
  `id` smallint(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar($max_region_name_len) COLLATE utf8_danish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;

CREATE TABLE IF NOT EXISTS `{$pf}commune` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `region_id` int(11) NOT NULL,
  `name` varchar($max_commune_name_len) COLLATE utf8_danish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;

CREATE TABLE IF NOT EXISTS `{$pf}city` (
  `id` smallint(11) unsigned NOT NULL AUTO_INCREMENT,
  `commune_id` int(11) NOT NULL,
  `name` varchar($max_city_name_len) COLLATE utf8_danish_ci NOT NULL,$sql_city_denormalized
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;

";
$sql .= array_to_sql($pf.'region', $regions);
$sql .= array_to_sql($pf.'commune', $communes);
$sql .= array_to_sql($pf.'city', $cities);

// Save file.
file_put_contents(DRD_FILE_OUT, $sql);

// Output status
echo "## Completed ##
  max_region_name_len = $max_region_name_len
  max_commune_name_len = $max_commune_name_len
  max_city_name_len = $max_city_name_len
";