<?php
// Errors on.
error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', 1);

// Globals.
define('DRD_ROOT', realpath(dirname(__FILE__)) . '/');
define('DRD_FILE_IN', DRD_ROOT . 'danish_regional_data.txt');
define('DRD_FILE_OUT', DRD_ROOT . 'danish_regional_data_lng_lat.txt');

/**
 * Reads the file danish_regional_data.txt and creates a new file
 * danish_regional_data_lng_lat.txt with latitude and longitude added to each
 * line.
 *
 * Uses the python script python multimap-danish-zipcode-search.py to get the
 * latitude and longitude.
 */
class DRD_LngLatFromZipcode {
    /**
     * Constructor.
     */
    function __construct() {
        $this->cleanOutputFile();
        $this->parseRows($this->readRows());
    }

    /**
     * Deletes content before writing to file.
     */
    function cleanOutputFile() {
        file_put_contents(DRD_FILE_OUT, '');
    }

    /**
     * Returns all rows from file.
     * 
     * @return array
     */
    function readRows() {
        return array_filter(explode("\n", file_get_contents(DRD_FILE_IN)));
    }

    /**
     * Loops over rows and writes to file.
     * 
     * @param array $rows
     */
    function parseRows($rows) {
        foreach($rows as $row) {
            $zipcode = $this->parseRow($row);
            $lng_lat = trim(`python multimap-danish-zipcode-search.py $zipcode`);
            file_put_contents(
                DRD_FILE_OUT,
                "$row $lng_lat\n",
                FILE_APPEND);
            sleep(1); // Don't hit mapfilter api too hard.
        }
    }

    /**
     * Returns zipcode of a single row.
     * 
     * @staticvar string $s
     * @staticvar string $n
     * @staticvar string $nn
     * @param string $row
     * @return string
     */
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

new DRD_LngLatFromZipcode;