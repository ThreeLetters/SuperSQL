<?php
/*
 Author: Andrews54757
 License: MIT (https://github.com/ThreeLetters/SuperSQL/blob/master/LICENSE)
 Source: https://github.com/ThreeLetters/SQL-Library
 Build: v2.5.0
 Built on: 08/08/2017
*/


class SQLHelper
{
    public $s;
    public $connections;
    function __construct($dt,$db = null,$user = null,$pass = null,$options = array())
    {
        $this->connections = array();
        $type = gettype($dt);
        if ($type == "array") {
            if (gettype($dt[0]) == "array") {
                foreach ($dt as $key => $v) {
                $host = isset($v["host"]) ? $v["host"] : "";
                $db = isset($v["db"]) ? $v["db"] : "";
                $user = isset($v["user"]) ? $v["user"] : "";
                $pass = isset($v["password"]) ? $v["password"] : "";
                $opt = isset($v["options"]) ? $v["options"] : array();
                $s = self::connect($host,$db,$user,$pass,$opt);
                array_push($this->connections,$s);
                }
            } else {
                 foreach ($dt as $key => $v) {
                    array_push($this->connections,$v);
                 }
            }
            $this->s = $this->connections[0];
        } else if ($type == "string") {
            $this->s = self::connect($dt,$db,$user,$pass,$options);
            array_push($this->connections,$this->s);
        } else {
            array_push($this->connections,$dt);
            $this->s = $dt;
        }
    }
    static function connect($host, $db, $user, $pass, $options = array())
    {
        $dbtype = "mysql";
        $dsn    = false;
        if (gettype($options) == "string") {
            if (strpos($options, ":") !== false) {
                $dsn = $options;
            } else {
                $dbtype = strtolower($options);
            }
        } else if (isset($options["dbtype"]))
            $dbtype = strtolower($options["dbtype"]);
        if (!$dsn) {
            $driver = "";
            switch ($dbtype) {
                case "pgsql":
                    $driver = "pgsql";
                    $data   = array(
                        "dbname" => $db,
                        "host" => $host
                    );
                    if (isset($options["port"]))
                        $data["port"] = $options["port"];
                    break;
                case "sybase":
                    $driver = "dblib";
                    $data   = array(
                        "dbname" => $db,
                        "host" => $host
                    );
                    if (isset($options["port"]))
                        $data["port"] = $options["port"];
                    break;
                case "oracle":
                    $driver = "oci";
                    $data   = array(
                        "dbname" => isset($host) ? "//" . $host . ":" . (isset($options["port"]) ? $options["port"] : "1521") . "/" . $db : $db
                    );
                    break;
                default:
                    $driver = "mysql";
                    $data   = array(
                        "dbname" => $db
                    );
                    if (isset($options["socket"]))
                        $data["unix_socket"] = $options["socket"];
                    else {
                        $data["host"] = $host;
                        if (isset($options["port"]))
                            $data["port"] = $options["port"];
                    }
                    break;
            }
            $dsn = $driver . ":";
            if (isset($options['charset'])) {
                $data['charset'] = $options['charset'];
            }
            $dsn = $driver . ":";
            $b   = 0;
            foreach ($data as $key => $val) {
                if ($b != 0) {
                    $dsn .= ";";
                }
                $dsn .= $key . "=" . $val;
                $b++;
            }
        }
        return new SuperSQL($dsn, $user, $pass);
    }
    private static function rmComments($str) {
        $i = strpos($str,"#");
        if ($i !== false) {
            $str = substr($str,0,$i);
        }
        return trim($str);
    }
    private static function escape($value) {
        $var = strtolower(gettype($value));
        if ($var == "boolean") {
            $value = $value ? "1" : "0";
        } else if ($var == "string") {
            $value = "'" . $value . "'";
        } else if ($var == "double") {
            $value = (int) $value;
        } else if ($var == "null") {
            $value = "0";
        }
        return $value;
    }
     private static function escape2($value) {
        if (is_numeric($value)) {
            return (int)$value;
        } else {
        return "'" . $value . "'";
        }
    }
    private static function includes($val,$arr) {
        foreach ($arr as $v) {
         if (strpos($val, $v) !== false) return true;
        }
        return false;
    }
    private static function containsAdv($arr) {
        foreach ($arr as $key => $val) {
           if (gettype($val) == "array") return true;
            if (self::includes($key,array("[","#"))) return true;
            if (self::includes($val,array("DISTINCT","INSERT INTO","INTO"))) return true;
        }
        return false;
    }
    function change($id) {
            $this->s = $this->connections[$id];
            return $this->s;
    }
    function getCon($all = false) {
        if ($all) {
            return $this->connections;
        } else {
         return $this->s;
        }
    }
    function get($table, $columns, $where, $join = null)
    {
        $d = $this->s->SELECT($table, $columns, $where, $join, 1)->getData();
        return ($d && $d[0]) ? $d[0] : false;
    }
    function create($table, $data)
    {
        $sql = "CREATE TABLE `" . $table . "` (";
        $i   = 0;
        foreach ($data as $key => $val) {
            if ($i != 0) {
                $sql .= ", ";
            }
            $sql .= "`" . $key . "` " . $val;
            $i++;
        }
        $sql .= ")";
        return $this->s->query($sql);
    }
    function drop($table)
    {
        return $this->s->query("DROP TABLE `" . $table . "`");
    }
    function replace($table,$data,$where = array()) {
        $newData = array();
        foreach ($data as $key => $val) {
            $str = "`" . self::rmComments($key) . "`";
            foreach ($val as $k => $v) {
                $str = "REPLACE(" . $str . ", " . self::escape2($k) . ", " . self::escape($v) . ")";
            }
            $newData["#" . $key] = $str;
        }
        return $this->s->UPDATE($table,$newData,$where);
    }
    function select($table,$columns = array(),$where = array(),$join = null,$limit = false) {
        if (gettype($table) == "array" || self::containsAdv($columns) || self::containsAdv($where) || $join) {
            return $this->s->SELECT($table,$columns,$where,$join,$limit);
        } else {
            if (gettype($limit) == "integer") $limit = "LIMIT " . (int)$limit;
            return $this->s->sSELECT($table,$columns,$where,$limit);
        }
    }
    function insert($table,$data) {
         if (gettype($table) == "array" || self::containsAdv($data)) {
            return $this->s->INSERT($table,$data);
         } else {
            return $this->s->sINSERT($table,$data);
         }      
    }
    function update($table,$data,$where = array()) {
        if (gettype($table) == "array" || self::containsAdv($data) || self::containsAdv($where)) {
            return $this->s->UPDATE($table,$data,$where);
         } else {
               return $this->s->sUPDATE($table,$data,$where);
         } 
    }
    function delete($table,$where = array()) {
         if (gettype($table) == "array" || self::containsAdv($where)) {
            return $this->s->DELETE($table,$where);
         } else {
            return $this->s->sDELETE($table,$where);
         } 
    }
}
