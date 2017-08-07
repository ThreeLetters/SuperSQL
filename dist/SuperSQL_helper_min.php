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
 Build: v2.0.0
 Built on: 06/08/2017
*/

class Helper{ public $s; function __construct($SuperSQL) { $this->s = $SuperSQL; } static function connect($host, $db, $user, $pass, $options = array()) { $dbtype = "mysql"; $dsn = false; if (gettype($options) == "string") { if (strpos($options, ":") !== false) { $dsn = $options; } else { $dbtype = strtolower($options); } } else if (isset($options["dbtype"])) $dbtype = strtolower($options["dbtype"]); if (!$dsn) { $driver = ""; switch ($dbtype) { case "pgsql": $driver = "pgsql"; $data = array( "dbname" => $db, "host" => $host ); if (isset($options["port"])) $data["port"] = $options["port"]; break; case "sybase": $driver = "dblib"; $data = array( "dbname" => $db, "host" => $host ); if (isset($options["port"])) $data["port"] = $options["port"]; break; case "oracle": $driver = "oci"; $data = array( "dbname" => isset($host) ? "//" . $host . ":" . (isset($options["port"]) ? $options["port"] : "1521") . "/" . $db : $db ); break; default: $driver = "mysql"; $data = array( "dbname" => $db ); if (isset($options["socket"])) $data["unix_socket"] = $options["socket"]; else { $data["host"] = $host; if (isset($options["port"])) $data["port"] = $options["port"]; } break; } $dsn = $driver . ":"; if (isset($options['charset'])) { $data['charset'] = $options['charset']; } $dsn = $driver . ":"; $b = 0; foreach ($data as $key => $val) { if ($b != 0) { $dsn .= ";"; } $dsn .= $key . "=" . $val; $b++; } } return new SuperSQL($dsn, $user, $pass); } static function get($table, $columns, $where, $join = null) { $d = $this->s->SELECT($table, $columns, $where, $join, 1)->getData(); return ($d && $d[0]) ? $d[0] : false; } static function create($table, $data) { $sql = "CREATE TABLE `" . $table . "` ("; $i = 0; foreach ($data as $key => $val) { if ($i != 0) { $sql .= ", "; } $sql .= "`" . $key . "` " . $val; $i++; } $sql .= ")"; return $s->query($sql); } static function drop($table) { return $s->query("DROP TABLE `" . $table . "`"); }}
?>