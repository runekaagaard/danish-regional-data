<?php
// Errors on.
error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', 1);

// Globals.
define('DRD_ROOT', realpath(dirname(__FILE__)) . '/');
define('DRD_FILE_IN', DRD_ROOT . 'danish_regional_data_lng_lat_modified.txt');
define('DRD_FILE_OUT', DRD_ROOT . 'danish_regional_data.json');

/**
 * Extracts data from text file.
 */
class DRD_RegionalData {
    /**
     * The extracted postalcodes.
     * @var array
     */
    public $postalcodes = array();
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
     * Joins tables.
     * @var arrays
     */
    public $postalcode_communes = array();
    public $commune_regions = array();

    /**
     * Constructor.
     */
    function __construct() {
        $this->cleanOutputFile();
        $this->parseRows($this->readRows());
        $this->sort();
    }

    function sort() {
        ksort($this->postalcodes);
        ksort($this->communes);
        ksort($this->regions);
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
        $postalcode_commune_id = 1;
        $commune_region_id = 1;
        foreach($rows as $row) {
            $row = $this->parseRow($row);
            extract($row);
            $commune_name = trim(str_ireplace('kommune', '', $commune_name));
            $this->regions[$region_id] = array('id' => $region_id,
                                               'name' => $region_name);
            $this->communes[$commune_id] = array('id' => $commune_id,
                                               'name' => $commune_name);
            $postalcode_commune = array(
                'postalcode_id' => $postalcode,
                'commune_id' => $commune_id,
            );
            if (!in_array($postalcode_commune, $this->postalcode_communes)) {
                $this->postalcode_communes[$postalcode_commune_id] =
                    $postalcode_commune;
            }
            $commune_region = array(
                'commune_id' => $commune_id,
                'region_id' => $region_id,
            );
            if (!in_array($commune_region, $this->commune_regions)) {
                $this->commune_regions[$commune_region_id] = $commune_region;
            }
            $this->defaultPostalCode($postalcode, $city);
            $this->postalcodes[$postalcode]['name'] = $city;
            $this->postalcodes[$postalcode]['lat'] = $lat;
            $this->postalcodes[$postalcode]['lng'] = $lng;
            if (!in_array($region_id, $this->postalcodes[$postalcode]['region_ids']))
            {
                $this->postalcodes[$postalcode]['region_ids'][] = $region_id;
                $this->postalcodes[$postalcode]['region_names'][] = $region_name;
            }
            if (!in_array($commune_id, $this->postalcodes[$postalcode]['commune_ids'])
            ){
                $this->postalcodes[$postalcode]['commune_ids'][] = $commune_id;
                $this->postalcodes[$postalcode]['commune_names'][] = $commune_name;
            }
            ++$postalcode_commune_id;
            ++$commune_region_id;
        }
    }

    /**
     * Sets default values for a postalcode.
     *
     * @param int $postalcode
     * @param string $city
     */
    function defaultPostalCode($postalcode, $city) {
        if (!isset($this->postalcodes[$postalcode])) {
            $this->postalcodes[$postalcode] = array(
                'id' => $postalcode,
                'name' => $city,
                'region_ids'=> array(),
                'region_names'=> array(),
                'commune_ids' => array(),
                'commune_names'=> array(),
            );
        }
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
          . "(?P<postalcode>$n)(?P<city>$nn)"
          . "(?P<lat>$f) (?P<lng>$f)/u"
          , $row, $ms)) 
        {
            die("Invalid row in file:\n");
            var_dump($row);
        }
        foreach ($ms as $k => &$m) {
            if (is_numeric($k)) unset($ms[$k]);
            $m = trim($m);
        }
        return $ms;
    }
}

/**
 * Adds the rows with postalcodes within a range to each postalcode.
 */
class DRD_AddSpatialData {
    /**
     * The ranges we want to find other postalcodes within.
     * @var array
     */
    public $ranges = array(5, 10, 25, 50, 100);

    /**
     * Constructor
     * @param DRD_RegionalData $regional_data
     */
    function __construct(DRD_RegionalData $regional_data) {
        foreach ($this->ranges as $range) {
            foreach ($regional_data->postalcodes as &$postalcode) {
                $this->addSpatialData($postalcode, $regional_data->postalcodes,
                                      $range);
            }
        }
    }

    /**
     * Adds rows like "within_XX_km" to each postalcode.
     *
     * @param array $postalcode_from
     * @param array $postalcodes
     * @param int $range
     */
    function addSpatialData(&$postalcode_from, $postalcodes, $range) {
        $key = 'within_' . $range . '_km';
        foreach ($postalcodes as $postalcode_to) {
            if ($this->inRange($postalcode_from, $postalcode_to, $range)) {
                $postalcode_from[$key][] = $postalcode_to['id'];
            }
        }
    }

    /**
     * Returns wether two postalcodes are located within given range.
     * 
     * @param array $postalcode_from
     * @param array $postalcode_to
     * @param array $range
     * @return bool
     */
    function inRange($postalcode_from, $postalcode_to, $range) {
        return $this->distance($postalcode_from['lat'], $postalcode_from['lng'],
                $postalcode_to['lat'], $postalcode_to['lng']) <= $range;
        
    }

    /**
     * Returns distance in km between two points.
     * 
     * @param float $lat1
     * @param float $lng1
     * @param float $lat2
     * @param float $lng2
     * @return float
     */
    function distance($lat1, $lng1, $lat2, $lng2) {
        $pi80 = M_PI / 180;
        $lat1 *= $pi80;
        $lng1 *= $pi80;
        $lat2 *= $pi80;
        $lng2 *= $pi80;
        $r = 6372.797; // mean radius of Earth in km
        $dlat = $lat2 - $lat1;
        $dlng = $lng2 - $lng1;
        $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2)
             * sin($dlng / 2) * sin($dlng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $km = $r * $c;
        return $km;
    }
}

/**
 * Writes json file to disk.
 */
class DRD_CreateJson {
    function __construct(DRD_RegionalData $regional_data) {
        $json = array(
            'postalcodes' => $regional_data->postalcodes,
            'communes' => $regional_data->communes,
            'regions' => $regional_data->regions,
            'postalcode_communes' => $regional_data->postalcode_communes,
            'commune_regions' => $regional_data->commune_regions,
        );
        file_put_contents(DRD_FILE_OUT, json_encode($regional_data));
    }
}

$regional_data = new DRD_RegionalData;
new DRD_AddSpatialData($regional_data);
new DRD_CreateJson($regional_data);