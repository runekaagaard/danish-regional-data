<?php
/**
 * Create insert sql for a table from given rows.
 *
 * @param string $table
 * @param array $rows
 * @return string
 */
function array_to_sql($table, $rows){
    $sql = "INSERT INTO `$table` (" . implode_padded(array_keys(current($rows))) . ") VALUES \n";
    $count = count($rows) - 1;
    $i = 0;
    foreach ($rows as $row) {
        $sql .= '(' . implode_padded($row, ',', "'") . ')';
        if ($i != $count) $sql .= ",";
        $sql .= "\n";
        ++$i;
    }
    $sql .= ";\n\n";

    return $sql;
}

/**
 * Version of implode that can pad each value before and after.
 *
 * @param array $array
 * @param string $delimiter
 * @param string $padding
 * @return string
 */
function implode_padded($array, $delimiter = ',', $padding = '`') {
    $str = '';
    foreach ($array as $v) $str .= $padding . $v . $padding . $delimiter;
    $str = rtrim($str, $delimiter);
    return $str;
}

/**
 * Convert an object into an associative array
 *
 * This function converts an object into an associative array by iterating
 * over its public properties. Because this function uses the foreach
 * construct, Iterators are respected. It also works on arrays of objects.
 *
 * @return array
 */
function object_to_array($var) {
    $result = array();
    $references = array();

    // loop over elements/properties
    foreach ($var as $key => $value) {
        // recursively convert objects
        if (is_object($value) || is_array($value)) {
            // but prevent cycles
            if (!in_array($value, $references)) {
                $result[$key] = object_to_array($value);
                $references[] = $value;
            }
        } else {
            // simple values are untouched
            $result[$key] = $value;
        }
    }
    return $result;
}

function flatten_array_key(&$array_of_arrays, $keys) {
    foreach ($array_of_arrays as &$array) {
        $flattened = ':';
        foreach ($keys as $key) {
            if (empty($array[$key])) {
                $array[$key] = '';
                continue;
            }
            $array[$key] = ':' . implode(':', $array[$key]) . ':';
        }
    }
}