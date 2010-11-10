<?php
// Errors on.
error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', 1);

// Settings, change these if you want.
define('DRD_ROOT', realpath(dirname(__FILE__)) . '/');
define('DRD_FILE_IN', DRD_ROOT . 'danish_regional_data.txt');
define('DRD_FILE_OUT', DRD_ROOT . 'danish_regional_data_lng_lat.txt');

class LngLatFromZipcode {
    function __construct() {
        $this->cleanOutputFile();
        $this->parseRows($this->readRows());
    }

    function cleanOutputFile() {
        file_put_contents(DRD_FILE_OUT, '');
    }
    
    function readRows() {
        return explode("\n", file_get_contents(DRD_FILE_IN));
    }

    function parseRows($rows) {
        foreach($rows as $row) {
            if (empty($row)) continue;
            $zipcode = $this->parseRow($row);
            $lng_lat = trim(`python multimap-danish-zipcode-search.py $zipcode`);
            file_put_contents(
                DRD_FILE_OUT,
                "$row $lng_lat\n",
                FILE_APPEND);
            sleep(1);
        }
    }

    function parseRow($row) {
        static $s = "[\ ]+";
        static $n = "[0-9]+";
        static $nn = "[^0-9]+";
        $row = trim($row);
        if (0 === preg_match("/(?P<region_id>$n){$s}Region{$s}(?P<region_name>$nn)(?P<commune_id>$n)(?P<commune_name>.*){$nn}(?P<city_id>$n)(?P<city_name>.*)/u", $row, $ms)) {
            die("Invalid file\n");
        }
        return $ms['city_id'];
    }
}

new LngLatFromZipcode;