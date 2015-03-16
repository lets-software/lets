<?php

class mysql {
/*  dependent on functions:
        function lastday($m);






*/
    var $connected,
        $result,
        $fields,
        $table,
        $primary_key,
        $num_rows,
        $inserted_id,
        $environment,
        $error;

    function mysql($environment = 'html') {
        if ($environment == 'cl') $this->environment = 'cl'; else $this->environment = 'html';
        $this->connected = 0;
    }
    function connect($host,$user,$password,$database) {
        if(!mysql_connect($host,$user,$password)) {
            $this->connected = 0;
            $this->error = "no host\n";
            return false;
        }
        if(!mysql_select_db("$database")) {
            $this->connected = 0;
            $this->error = "no database\n";
            return false;
        }
        $this->connected = 1;
        return true;
    }

    function clear() {
        if (isset($this->result))       unset($this->result);
        if (isset($this->num_rows))     unset($this->num_rows);
        if (isset($this->inserted_id))  unset($this->inserted_id);
        if (isset($this->error))        unset($this->error);
        if (isset($this->primary_key))  unset($this->primary_key);
        if (isset($this->fields))       unset($this->fields);
    }
    function end_line() {
        if ($this->environment == 'cl') return "\n";
        if ($this->environment == 'html') return '<br />';
    }
    function update_cell($table,$primary_field,$primary_value,$field_to_update,$value) {
        $q = 'UPDATE '.$table.' SET '.$field_to_update.' = '.$value.' WHERE '.$primary_field.' = '.$primary_value.' LIMIT 1';
        if (!mysql_query($q)) {
            $this->error = 'There was a mysql error:'.$this->end_line().mysql_error().$this->end_line().'IN: '.$q.$this->end_line();
            return false;
        } else {
            return true;
        }
    }
    function insert($q) {
        if (!mysql_query($q)) {
            $this->error = 'There was a mysql error:'.$this->end_line().mysql_error().$this->end_line().'IN: '.$q.$this->end_line();
            return false;
        } else {
            $this->inserted_id = mysql_insert_id();
            return true;
        }
    }
    function get_fields($t) {
        $q = 'SELECT * FROM '.$t.' WHERE 1 LIMIT 1';

        if (!mysql_query($q)) {
            $this->error = 'There was an error acquiring metadata for table: '.$t.':'.$this->end_line().mysql_error().$this->end_line().'IN: '.$q.$this->end_line();
            return false;
        }
        $key_query = mysql_query($q);
        $keys = array();
        $i = 0;
        while ($i < mysql_num_fields($key_query)) {
            $meta = mysql_fetch_field($key_query,$i);
            if ($meta->primary_key) $this->primary_key = $meta->name;
            $keys[$i]['name'] = $meta->name;
            $keys[$i]['blob'] = $meta->blob;
            $keys[$i]['max_length'] = $meta->max_length;
            $keys[$i]['multiple_key'] = $meta->multiple_key;
            $keys[$i]['not_null'] = $meta->not_null;
            $keys[$i]['numeric'] = $meta->numeric;
            $keys[$i]['unique_key'] = $meta->unique_key;
            $keys[$i]['unsigned'] = $meta->unsigned;
            $keys[$i]['zerofill'] = $meta->zerofill;
            $keys[$i]['primary_key'] = $meta->primary_key;
            $i++;
        }
        $this->fields = $keys;
        return true;
    }

    function insert_values($t,$va) {
        // table, array of values and their associated fields
        // array format required: $var[key][name] for the name of the field and $var[key][value] for its value, single pairs must use key eg: $var[0][name]
        if (empty($t) or !is_array($va)) {
            $this->error = array_report(array( 0 => array( 'name' => 'Values for mysql insert', 'body' => $va )));
            return false;
        }

        // get field data if it's not already set
        if (!is_array($this->fields)) if (!$this->get_fields($t)) return false;

        // build query
        $insert_query = 'INSERT INTO '.$t.' VALUES ( ';
        $num_fields = count($this->fields);
        for($i=0;$i<$num_fields;$i++) {
            $matched = 0;
            foreach($va as $v) {
                if ($this->fields[$i]['name'] == $v['name']) {
                    $matched = 1;
                    if ($this->fields[$i]['primary_key']) {
                        $this->error = 'This function should not be called with a value associated with the primary key'.$this->end_line();
                        return false;
                    } else {
                        if ($i == ($num_fields - 1)) {
                            $insert_query .= "'".$v['value']."'";
                        } else {
                            $insert_query .= "'".$v['value']."',";
                        }
                    }
                }
            }
            if (!$matched) {
                if ($i == ($num_fields - 1)) {
                    $insert_query .= "''";
                } else {
                    $insert_query .= "'',";
                }
            }
        }
        $insert_query .= ' )';

        // insert
        if ($this->insert($insert_query)) {
            $this->message = 'Insertion successfull'.$this->end_line();
            return true;
        } else {
            return false;
        }
    }

    function query($q) {
        if (!mysql_query($q)) {
            $this->error = 'There was a mysql error:'.$this->end_line().mysql_error().$this->end_line().'IN: '.$q.$this->end_line();
            return false;
        } else {
            return true;
        }
    }
    function num_rows($q) {
        $mysql_tools_num_rows = mysql_query($q);
        if (!$mysql_tools_num_rows) {
            $this->error = 'There was a mysql error:'.$this->end_line().mysql_error().$this->end_line().'IN: '.$q.$this->end_line();
            return false;
        } else {
            $this->num_rows = mysql_num_rows($mysql_tools_num_rows);
            mysql_free_result($mysql_tools_num_rows);
            return true;
        }
    }
    function parse_table($q) {
        if (strpos($q,'FROM ')) {
            $qe = explode('FROM ',$q);
            if (strpos($qe[1],' ')) {
                $qe = explode(' ',$qe[1]);
                $this->table = $qe[0];
                return true;
            } else {
                $this->table = $qe[1];
                return true;
            }
        }
        if (strpos($q,'From ')) {
            $qe = explode('From ',$q);
            if (strpos($qe[1],' ')) {
                $qe = explode(' ',$qe[1]);
                $this->table = $qe[0];
                return true;
            } else {
                $this->table = $qe[1];
                return true;
            }
        }
        if (strpos($q,'from ')) {
            $qe = explode('from ',$q);
            if (strpos($qe[1],' ')) {
                $qe = explode(' ',$qe[1]);
                $this->table = $qe[0];
                return true;
            } else {
                $this->table = $qe[1];
                return true;
            }
        }
        $this->error = 'Query does not contain a table name'.$this->end_line();
        return false;
    }
    function build_array($q,$f = 0) {
        if (!$this->parse_table($q)) return false;

        // get field data if it's not already set
        if (empty($this->fields)) if (!$this->get_fields($this->table)) return false;

        $mysql_tools_build_array = mysql_query($q);
        if (!$mysql_tools_build_array) {
            $this->error = 'There was a mysql error:'.$this->end_line().mysql_error().$this->end_line().'IN: '.$q.$this->end_line();
            return false;
        } elseif (mysql_num_rows($mysql_tools_build_array) == 0) {
            $this->num_rows = 0;
            $this->result = '';
            return true;
        } else {
            $this->num_rows = mysql_num_rows($mysql_tools_build_array);
            $counter = 0;
            $temp_array = array();
            while ($mysql_array = mysql_fetch_array($mysql_tools_build_array)) {
                foreach($this->fields as $key) {
                    if (!empty($f)) {
                        if ($f == $key['name']) {
                            if (isset($mysql_array[$key['name']])) {
                                $temp_array[$counter] = $mysql_array[$key['name']];
                            }
                        }
                    } else {
                        if (isset($mysql_array[$key['name']])) {
                            $temp_array[$counter][$key['name']] = $mysql_array[$key['name']];
                        }
                    }
                }
                $counter++;
            }
            $this->result = stripslashes_deep($temp_array);
            mysql_free_result($mysql_tools_build_array);
            return true;
        }
    }


    /**
     * Check if we got a result for a given SQL query
     *
     * @param $q    Query to check if we got a result
     *
     * @return bool    false = no result, true = result found
     */
     function result($q) {
        $mysql_tools_result = mysql_query($q);
        if ($mysql_tools_result === FALSE) {
            //$this->error = 'There was a mysql error:'.$this->end_line().mysql_error().$this->end_line().'IN: '.$q.$this->end_line();
            return false;
        } elseif (mysql_num_rows($mysql_tools_result) == 0) {
            $this->error = 'No rows were returned'.$this->end_line();
            return false;
        } else {
            $this->result = '';
            $temp_array = array();
            $num_fields = mysql_num_fields($mysql_tools_result);
            for ($i=0;$i<$num_fields;$i++) {
                $field_name = mysql_field_name($mysql_tools_result,$i);
                $temp_array[$field_name] = mysql_result($mysql_tools_result,0,$field_name);
            }
            $this->result = stripslashes_deep($temp_array);
            return true;
        }
    }
}

?>