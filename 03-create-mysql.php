<?php

// Errors on.
error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', 1);

// Globals.
define('DRD_ROOT', realpath(dirname(__FILE__)) . '/');
define('DRD_FILE_IN', DRD_ROOT . 'danish_regional_data.json');
#define('DRD_FILE_OUT', DRD_ROOT . 'danish_regional_data.json');

// Includes.
require DRD_ROOT . '/lib.php';

// Cache to array call.
$file = '/tmp/drd_regional_data_all.cache';
if (!file_exists($file)) {
    $data = object_to_array(json_decode(file_get_contents(DRD_FILE_IN)));
    file_put_contents($file, '<?php $data = ' . var_export($data, TRUE) . ';');
}
require $file;
flatten_array_key($data['postalcodes'], array('commune_ids', 'commune_names', 'region_ids', 'region_names', 'within_5_km', 'within_10_km', 'within_25_km', 'within_50_km', 'within_100_km'));
extract($data);

/*foreach ($data['postalcodes'] as $zipcode) {
    var_dump($zipcode); die;
}*/


#var_dump($data); die;
#ob_start();
require DRD_ROOT . 'mysql_template.php';
