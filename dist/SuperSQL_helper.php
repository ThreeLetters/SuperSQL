<?php
/*
MIT License

Copyright (c) 2017 Andrew S

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

*/

/*
 Author: Andrews54757
 License: MIT
 Source: https://github.com/ThreeLetters/SQL-Library
 Build: v2.5.0
 Built on: 07/08/2017
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
