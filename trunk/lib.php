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