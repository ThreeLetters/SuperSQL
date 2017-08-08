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
}
