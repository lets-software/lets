<?php


class mysqlPDO {
    var $connected,
            $result,
            $fields,
            $table,
            $primary_key,
            $num_rows,
            $inserted_id,
            $environment,
            $error;

    function connect($host,$user,$password,$database){
        try {

            $PDO = new PDO("mysql:host=$host;dbname=$database", $user , $password );
            $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch (PDOException $e) {
            $this->connected = 0;
            $this->error = "Unable to connect to the database\n";
            return false;
        }
    }
}
?>