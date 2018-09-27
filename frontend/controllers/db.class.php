<?php
$db = new mysqli('localhost', 'root', '', 'facturi');
$db->set_charset("utf8");
global $db;
if($db->connect_error) {
    die("Nu se poate conecta la baza de date: " . $connect->connect_error);
}

class Facturi_DB {

    function query($query) {

        global $db;

        $db->query($query);

    }

    function insert($table, $data, $lastid = null) {

        global $db;

        $formats = array();
        foreach ( $data as $value ) {
            $formats[] = '\''.$value.'\'';
        }
        $values = implode( ', ', $formats );
        $columns = '`' . implode( '`, `', array_keys( $data ) ) . '`';
        $query = "INSERT INTO `{$table}` ({$columns}) VALUES ({$values});";

        $db->query($query);

        if($lastid) {
            return $db->insert_id;
        }

    }

    function update($table, $data, $where) {

        global $db;

        $datadb = '';
        foreach($data as $column => $value) {
            $datadb .= "`".$column."` = '{$value}', ";
        }
        $datadb = rtrim($datadb, ', ');

        $wheredb = '';
        foreach ( $where as $column => $value ) {
            $wheredb .= $column." = '{$value}' AND ";
        }
        $wheredb = rtrim($wheredb, ' AND ');

        $query = "UPDATE `{$table}` SET {$datadb} WHERE {$wheredb};";

        $db->query($query);

    }

    function delete($table, $where) {

        global $db;

        $wheredb = '';
        foreach ( $where as $column => $value ) {
            $wheredb .= $column." = '{$value}' AND ";
        }
        $wheredb = rtrim($wheredb, ' AND ');

        $query = "DELETE FROM `{$table}` WHERE {$wheredb};";

        $db->query($query);

    }

    function get_var($query) {

        global $db;

        $query = $db->query($query)->fetch_object();

        if($query) {
            $query = get_object_vars($query);
            return $query[key($query)];
        } else {
            return null;
        }

    }

    function get_results($query) {

        global $db;

        $query = $db->query($query);
        $result = array();
        if($query) {
            while ($row = $query->fetch_assoc()) {
                $result[] = $row;
            }
        }

        if($result) {
            return $result;
        } else {
            return null;
        }

    }

}
$dbo = new Facturi_DB;
global $dbo;
?>
