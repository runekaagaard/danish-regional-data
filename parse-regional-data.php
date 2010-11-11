<?php
// Errors on.
error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', 1);

// Globals.
define('DRD_ROOT', realpath(dirname(__FILE__)) . '/');
define('DRD_FILE_IN', DRD_ROOT . 'danish_regional_data_lng_lat_modified.txt');
define('DRD_FILE_OUT', DRD_ROOT . 'danish_regional_data_lng_lat.txt');

/**
 * Extracts data from text file.
 */
class DRD_RegionalData {
    /**
     * The extracted zipcodes.
     * @var array
     */
    public $zipcodes = array();
    /**
     * The extracted communes.
     * @var array
     */
    public $communes = array();
    /**
     * The extracted regions.
     * @var array
     */
    public $regions = array();

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
            
            $row = $this->parseRow($row);
            extract($row);
            $this->regions[$region_id] = array('id' => $region_id,
                                               'name' => $region_name);
            $this->communes[$commune_id] = array('id' => $commune_id,
                                               'name' => $commune_name);
            if (!isset($this->zipcodes[$zipcode])) { 
                $this->zipcodes[$zipcode] = array(
                    'id' => $zipcode,
                    'name' => $city,
                    'region_ids'=> array(),
                    'region_names'=> array(),
                    'commune_ids' => array(),
                    'commune_names'=> array(),
                );
            }
            $this->zipcodes[$zipcode]['name'] = $city;
            $this->addToZipcodes($zipcode, $region_id, $region_name,
                                 $commune_id, $commune_name);
        }
    }

    function addToZipcodes($zipcode, $region_id, $region_name, $commune_id,
    $commune_name) {
        $this->zipcodes[$zipcode]['region_ids'][] = $region_id;
        $this->zipcodes[$zipcode]['region_names'][] = $region_name;
        $this->zipcodes[$zipcode]['commune_ids'][] = $commune_id;
        $this->zipcodes[$zipcode]['commune_names'][] = $commune_name;
    }

    /**
     * Returns array of rows parts.
     *
     * @staticvar string $s
     * @staticvar string $n
     * @staticvar string $nn
     * @param string $row
     * @return array
     */
    function parseRow($row) {
        static $s = "[\ ]+";
        static $n = "[0-9]+";
        static $f = "[0-9.]+";
        static $nn = "[^0-9]+";
        $row = trim($row);
        if (0 === preg_match(
          "/(?P<region_id>$n){$s}Region{$s}"
          . "(?P<region_name>$nn)(?P<commune_id>$n)(?P<commune_name>$nn){$nn}"
          . "(?P<zipcode>$n)(?P<city>$nn)"
          . "(?P<latitude>$f) (?P<longitude>$f)/u"
          , $row, $ms)) 
        {
            var_dump($row);
            die("Invalid file\n");
        }
        foreach ($ms as $k => &$m) {
            if (is_numeric($k)) unset($ms[$k]);
            $m = trim($m);
        }
        return $ms;
    }
}

class DRD_CreateSql {
    function __construct(DRD_RegionalData $regional_data) {
        
    }
}
$regional_data = new DRD_RegionalData;
/*var_dump($regional_data->zipcodes[4000]);
var_dump($regional_data->regions[1084]);
var_dump($regional_data->communes[169]);*/
new DRD_CreateSql($regional_data);