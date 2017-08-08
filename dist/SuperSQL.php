<?php
/*
 Author: Andrews54757
 License: MIT (https://github.com/ThreeLetters/SuperSQL/blob/master/LICENSE)
 Source: https://github.com/ThreeLetters/SQL-Library
 Build: v1.0.0
 Built on: 08/08/2017
*/

// lib/connector/index.php
class Response
{
    public $result;
    public $affected;
    public $ind;
    public $error;
    public $errorData;
    function __construct($data, $error)
    {
        $this->error = !$error;
        if (!$error) {
            $this->errorData = $data->errorInfo();
        } else {
            $this->result   = $data->fetchAll();
            $this->affected = $data->rowCount();
        }
        $this->ind = 0;
        $data->closeCursor();
    }
    function error()
    {
        return $this->error ? $this->errorData : false;
    }
    function getData()
    {
        return $this->result;
    }
    function getAffected()
    {
        return $this->affected;
    }
    function next()
    {
        return $this->result[$this->ind++];
    }
    function reset()
    {
        $this->ind = 0;
    }
}
class Connector
{
    public $queries = array();
    public $db;
    public $log = array();
    public $dev = false;
    function __construct($dsn, $user, $pass)
    {
        $this->db  = new \PDO($dsn, $user, $pass);
        $this->log = array();
    }
    function query($query,$obj = null)
    {
        if (isset($this->queries[$query])) {
            $q = $this->queries[$query];
        } else {
            $q = $this->db->prepare($query);
            $this->queries[$query] = $q;
        }
        if ($obj) $e = $q->execute($obj);
        else $e = $q->execute();
        if ($this->dev)
            array_push($this->log, array(
                $query,
                $obj
            ));
        return new Response($q,$e);
    }
    function _query($sql, $values, $insert, $typeString)
    {
        if (isset($this->queries[$sql . "|" . $typeString])) { 
            $s = $this->queries[$sql . "|" . $typeString];
            $q = $s[1];
            $v = &$s[0];
            foreach ($values as $key => $vq) {
                $v[$key][0] = $vq[0];
            }
            if ($this->dev)
                array_push($this->log, array(
                    "fromcache",
                    $sql,
                    $typeString,
                    $values,
                    $insert
                ));
        } else {
            $q                   = $this->db->prepare($sql);
             $v = $values;
            foreach ($v as $key => &$va) {
                $q->bindParam($key + 1, $va[0],$va[1]);
            }
            $this->queries[$sql . "|" . $typeString] = array(&$v,$q);
            if ($this->dev)
                array_push($this->log, array(
                    $sql,
                    $typeString,
                    $values,
                    $insert
                ));
        }
        if (count($insert) == 0) { 
            $e = $q->execute();
            return new Response($q, $e);
        } else { 
            $responses = array();
            $e = $q->execute();
            array_push($responses,new Response($q, $e));
            foreach ($insert as $key => $value) {
                foreach ($value as $k => $val) {
                    $v[$k][0] = $val;
                }
                $e = $q->execute();
                array_push($responses, new Response($q, $e));
            }
            return $responses;
        }
    }
    function close()
    {
        $this->db      = null;
        $this->queries = null;
    }
    function clearCache()
    {
        $this->queries = array();
    }
}

// lib/parser/Simple.php
class SimParser
{
    public static function WHERE($where, &$sql, &$insert)
    {
        if (count($where) != 0) {
            $sql .= " WHERE ";
            $i = 0;
            foreach ($where as $key => $value) {
                if ($i != 0) {
                    $sql .= " AND ";
                }
                $sql .= "`" . $key . "` = ?";
                array_push($insert,$value);
                $i++;
            }
        }
    }
    public static function SELECT($table, $columns, $where, $append)
    {
        $sql    = "SELECT ";
        $insert = array();
        $len    = count($columns);
        if ($len == 0) { 
            $sql .= "*";
        } else { 
            for ($i = 0; $i < $len; $i++) {
                if ($i != 0) {
                    $sql .= ", ";
                }
                $sql .= "`" . $columns[$i] . "`";
            }
        }
        $sql .= " FROM `" . $table . "`";
        self::WHERE($where, $sql, $insert);
       if ($append) $sql .= " " . $append;
        return array(
            $sql,
            $insert
        );
    }
    public static function INSERT($table, $data)
    {
        $sql    = "INSERT INTO `" . $table . "` (";
        $add    = ") VALUES (";
        $insert = array();
        $i = 0;
        foreach ($data as $key => $value) {
            if ($i != 0) {
                $sql .= ", ";
                $add .= ", ";
            }
            $sql .= "`" . $key . "`";
            $add .= "?";
            array_push($insert, $value);
            $i++;
        }
        $sql .= $add . ")";
        return array(
            $sql,
            $insert
        );
    }
    public static function UPDATE($table, $data, $where)
    {
        $sql    = "UPDATE `" . $table . "` SET ";
        $insert = array();
        $i = 0;
        foreach ($data as $key => $value) {
            if ($i != 0) {
                $sql .= ", ";
            }
            $sql .= "`" . $key . "` = ?";
            array_push($insert, $value);
            $i++;
        }
        self::WHERE($where, $sql, $insert);
        return array(
            $sql,
            $insert
        );
    }
    public static function DELETE($table, $where)
    {
        $sql    = "DELETE FROM `" . $table . "`";
        $insert = array();
        self::WHERE($where, $sql, $insert);
        return array(
            $sql,
            $insert
        );
    }
}

// lib/parser/Advanced.php
class AdvParser
{
    private static function getArg(&$str)
    {
        if (substr($str, 0, 1) == "[" && substr($str, 3, 1) == "]") {
            $out = substr($str, 1, 2);
            $str = substr($str, 4);
            return $out;
        } else {
            return false;
        }
    }
    private static function append(&$args, $val, $index)
    {
        if (gettype($val) == "array") {
            $len = count($val);
            for ($k = 1; $k < $len; $k++) {
                if (!isset($args[$k]))
                    $args[$k] = array();
                $args[$k][$index] = $val[$k];
            }
        }
    }
    private static function append2(&$insert, $indexes, $dt)
    {
        function stripArgs(&$key)
        {
            if (substr($key, -1) == "]") {
                $b   = strrpos($key, "[", -1);
                $key = substr($key, 0, $b);
            }
            $b = strrpos($key, "]", -1);
            if ($b !== false)
                $key = substr($key, $b + 1);
        }
        function recurse(&$holder, $val, $indexes, $par)
        {
            foreach ($val as $k => $v) {
                if (gettype($v) == "array") {
                    stripArgs($k);
                    if (isset($v[0])) {
                        if (isset($indexes[$k . "#" . $par . "*"]))
                            $d = $indexes[$k . "#" . $par . "*"];
                        else
                            $d = $indexes[$k . "*"];
                        foreach ($v as $i => $j) {
                            $holder[$d + $i] = $j;
                        }
                    } else {
                        recurse($holder, $v, $indexes, $par . "/" . $k);
                    }
                } else {
                    stripArgs($k);
                    if (isset($indexes[$k . "#" . $par]))
                        $d = $indexes[$k . "#" . $par];
                    else
                        $d = $indexes[$k];
                    $holder[$d] = $v;
                }
            }
        }
        $len = count($dt);
        for ($key = 1; $key < $len; $key++) {
            $val = $dt[$key];
            if (!isset($insert[$key]))
                $insert[$key] = array();
            recurse($insert[$key], $val, $indexes, "");
        }
    }
    private static function quote($str)
    {
        $str = explode(".", $str);
        $out = "";
        for ($i = 0; $i < count($str); $i++) {
            if ($i != 0)
                $out .= ".";
            $out .= "`" . $str[$i] . "`";
        }
        return $out;
    }
    private static function table($table)
    {
        if (gettype($table) == "array") {
            $sql = "";
            for ($i = 0; $i < count($table); $i++) {
                $t = self::getType($table[$i]);
                if ($i != 0)
                    $sql .= ", ";
                $sql .= self::quote($table[$i]);
                if ($t)
                    $sql .= " AS " . self::quote($t);
            }
            return $sql;
        } else {
            return self::quote($table);
        }
    }
    private static function value($type, $value, &$typeString)
    {
        $var = strtolower($type);
        if (!$var)
            $var = strtolower(gettype($value));
        $type = \PDO::PARAM_INT;
        if ($var == "boolean" || $var == "bool") {
            $type  = \PDO::PARAM_BOOL;
            $value = $value ? "1" : "0";
            $typeString .= "b";
        } else if ($var == "integer" || $var == "int") {
            $typeString .= "i";
            $value = (int) $value;
        } else if ($var == "string" || $var == "str") {
            $type = \PDO::PARAM_STR;
            $value = (string) $value;
            $typeString .= "s";
        } else if ($var == "double" || $var == "doub") {
            $value = (int) $value;
            $typeString .= "i";
        } else if ($var == "resource" || $var == "lob") {
            $type = \PDO::PARAM_LOB;
            $typeString .= "l";
        } else if ($var == "null") {
            $type  = \PDO::PARAM_NULL;
            $value = null;
            $typeString .= "n";
        }
        return array(
            $value,
            $type
        );
    }
    private static function getType(&$str)
    {
        if (substr($str, -1) == "]") {
            $start = strpos($str, "[");
            if ($start === false) {
                return "";
            }
            $out = substr($str, $start + 1, -1);
            $str = substr($str, 0, $start);
            return $out;
        } else
            return "";
    }
    private static function rmComments($str) {
        $i = strpos($str,"#");
        if ($i !== false) {
            $str = substr($str,0,$i);
        }
        return trim($str);
    }
    private static function conditions($dt, &$values = false, &$map = false, &$typeString = "", &$index = 0)
    {
        $build = function(&$build, $dt, &$map, &$index, &$values, &$typeString, $join = " AND ", $operator = " = ", $parent = "")
        {
            $num = 0;
            $sql = "";
            foreach ($dt as $key => $val) {
                if (substr($key, 0, 1) === "#") {
                    $raw = true;
                    $key = substr($key, 1);
                } else {
                    $raw = false;
                }
                $arg         = self::getArg($key);
                $arg2        = $arg ? self::getArg($key) : false;
                $valType     = gettype($val);
                $useBind     = !isset($val[0]);
                $newJoin     = $join;
                $newOperator = $operator;
                switch ($arg) {
                    case "||":
                        $arg     = $arg2;
                        $newJoin = " OR ";
                        break;
                    case "&&":
                        $arg     = $arg2;
                        $newJoin = " AND ";
                        break;
                }
                switch ($arg) { 
                    case ">>":
                        $newOperator = " > ";
                        break;
                    case "<<":
                        $newOperator = " < ";
                        break;
                    case ">=":
                        $newOperator = " >= ";
                        break;
                    case "<=":
                        $newOperator = " <= ";
                        break;
                    case "!=":
                        $newOperator = " != ";
                        break;
                    case "~~":
                        $newOperator = " LIKE ";
                        break; 
                    case "!~":
                        $newOperator = " NOT LIKE ";
                        break; 
                    default:
                        if (!$useBind || $arg == "==")
                            $newOperator = " = "; 
                        break;
                }
                if ($num != 0)
                    $sql .= $join;
                if ($valType == "array") {
                    if ($useBind) {
                        $sql .= "(" . $build($build, $val, $map, $index, $values, $newJoin, $newOperator, $parent . "/" . $key) . ")";
                    } else {
                        $type = self::getType($key);
                        $c = self::rmComments($key);
                        if ($map !== false && !$raw) {
                            $map[$key . "*"]                 = $index;
                            $map[$key . "#" . $parent . "*"] = $index++;
                        }
                        foreach ($value as $k => $v) {
                            if ($k != 0)
                                $sql .= $newJoin;
                            $index++;
                            if ($raw) {
                                $sql .= self::quote($c) . $newOperator . $v;
                            } else if ($values !== false) {
                                $sql .= "`" . $c . "`" . $newOperator . "?";
                                array_push($values, self::value($type, $v, $typeString));
                            } else {
                                $sql .= self::quote($c) . $newOperator;
                                if (gettype($v) == "integer") {
                                    $sql .= $v;
                                } else {
                                    $sql .= self::quote($v);
                                }
                            }
                        }
                    }
                } else {
                    if ($raw) {
                          $sql .= self::quote(self::rmComments($key)) . $newOperator . $val;
                    } else {
                        if ($values !== false) {
                            $t = self::getType($key);
                            $sql .= "`" . self::rmComments($key) . "`" . $newOperator . "?";
                            array_push($values, self::value($t, $val, $typeString));
                        } else {
                            $sql .= self::quote(self::rmComments($key)) . $newOperator;
                            if (gettype($val) == "integer") {
                                $sql .= $val;
                            } else {
                                $sql .= self::quote($val);
                            }
                        }
                        if ($map !== false) {
                            $map[$key]                 = $index;
                            $map[$key . "#" . $parent] = $index++;
                        }
                    }
                }
                return $sql;
            }
            $num++;
        };
        return $build($build, $dt, $map, $index, $values, $typeString);
    }
    static function SELECT($table, $columns, $where, $join, $limit)
    {
        $sql = "SELECT ";
        $len = count($columns);
        $values = array();
        $insert = array();
        if ($len == 0) { 
            $sql .= "*";
        } else { 
            $i    = 0;
            $req  = 0;
            $into = "";
            if ($columns[0] == "DISTINCT") {
                $i   = 1;
                $req = 1;
                $sql .= "DISTINCT ";
            } else if (substr($columns[0], 0, 11) == "INSERT INTO") {
                $i   = 1;
                $req = 1;
                $sql = $columns[0] . " " . $sql;
            } else if (substr($columns[0], 0, 4) == "INTO") {
                $i    = 1;
                $req  = 1;
                $into = " " . $columns[0] . " ";
            }
            if ($len > $req) { 
                for (; $i < $len; $i++) {
                    $t = self::getType($columns[$i]);
                    if ($i > $req) {
                        $sql .= ", ";
                    }
                    $sql .= self::quote($columns[$i]);
                    if ($t)
                        $sql .= " AS `" . $t . "`";
                }
            } else
                $sql .= "*";
            $sql .= $into;
        }
        $sql .= " FROM " . self::table($table);
        if ($join) {
            foreach ($join as $key => $val) {
                if (substr($key, 0, 1) === "#") {
                    $raw = true;
                    $key = substr($key, 1);
                } else {
                    $raw = false;
                }
                $arg = self::getArg($key);
                switch ($arg) {
                    case "<<":
                        $sql .= " RIGHT JOIN ";
                        break;
                    case ">>":
                        $sql .= " LEFT JOIN ";
                        break;
                    case "<>":
                        $sql .= " FULL JOIN ";
                        break;
                    default: 
                        $sql .= " JOIN ";
                        break;
                }
                $sql .= self::quote($key) . " ON ";
                if ($raw) {
                    $sql .= "val";
                } else {
                    $sql .= self::conditions($val);
                }
            }
        }
        $typeString = "";
        if (count($where) != 0) {
            $sql .= " WHERE ";
            $index = array();
            if (isset($where[0])) {
                $sql .= self::conditions($where[0], $values, $index, $typeString);
                self::append2($insert, $index, $where);
            } else {
                $sql .= self::conditions($where, $values, $index, $typeString);
            }
        }
        if ($limit) {
            if (gettype($limit) == "integer") {
                 $sql .= " LIMIT " . $limit; 
            } else if (gettype($limit) == "string") {
                 $sql .= " " . $limit; 
            }
        }
        return array(
            $sql,
            $values,
            $insert,
            $typeString
        );
    }
    static function INSERT($table, $data)
    {
        $sql        = "INSERT INTO " . self::table($table) . " (";
        $values     = array();
        $insert     = array();
        $typeString = "";
        $append     = "";
        $i       = 0;
        $b       = 0;
        $indexes = array();
        $multi   = isset($data[0]);
        $dt      = $multi ? $data[0] : $data;
        foreach ($dt as $key => $val) {
            if (substr($key, 0, 1) === "#") {
                $raw = true;
                $key = substr($key, 1);
            } else {
                $raw = false;
            }
            if ($b != 0) {
                $sql .= ", ";
                $append .= ", ";
            }
            $type = self::getType($key);
            $sql .= "`" . self::rmComments($key) . "`";
            if ($raw) {
                $append .= $val;
            } else {
                $append .= "?";
                array_push($values, self::value($type, $val, $typeString));
                if ($multi) {
                    $indexes[$key] = $i++;
                } else {
                    self::append($insert, $val, $i++);
                }
            }
            $b++;
        }
        if ($multi)
            self::append2($insert, $indexes, $data);
        $sql .= ") VALUES (" . $append . ")";
        return array(
            $sql,
            $values,
            $insert,
            $typeString
        );
    }
    static function UPDATE($table, $data, $where)
    {
        $sql        = "UPDATE " . self::table($table) . " SET ";
        $values     = array();
        $insert     = array();
        $typeString = "";
        $i          = 0;
        $b          = 0;
        $indexes    = array();
        $multi      = isset($data[0]);
        $dt         = $multi ? $data[0] : $data;
        foreach ($dt as $key => $val) {
            if (substr($key, 0, 1) === "#") {
                $raw = true;
                $key = substr($key, 1);
            } else {
                $raw = false;
            }
            if ($b != 0) {
                $sql .= ", ";
            }
            if ($raw) {
                $sql .= "`" . $key . "` = " . $val;
            } else {
                $arg = self::getArg($key);
                $sql .= "`" . $key . "` = ";
                switch ($arg) {
                    case "+=":
                        $sql .= "`" . $key . "` + ?";
                        break;
                    case "-=":
                        $sql .= "`" . $key . "` - ?";
                        break;
                    case "/=":
                        $sql .= "`" . $key . "` / ?";
                        break;
                    case "*=":
                        $sql .= "`" . $key . "` * ?";
                        break;
                    default:
                        $sql .= "?";
                        break;
                }
                $type = self::getType($key);
                array_push($values, self::value($type, $val, $typeString));
                if ($multi) {
                    $indexes[$key] = $i++;
                } else {
                    self::append($insert, $val, $i++);
                }
            }
            $b++;
        }
        if ($multi)
            self::append2($insert, $indexes, $data);
        if (count($where) != 0) {
            $sql .= " WHERE ";
            $index = array();
            if (isset($where[0])) {
                $sql .= self::conditions($where[0], $values, $index, $typeString, $i);
                self::append2($insert, $index, $where);
            } else {
                $sql .= self::conditions($where, $values, $index, $typeString, $i);
            }
        }
        return array(
            $sql,
            $values,
            $insert,
            $typeString
        );
    }
    static function DELETE($table, $where)
    {
        $sql        = "DELETE FROM " . self::table($table);
        $values     = array();
        $insert     = array();
        $typeString = "";
        if (count($where) != 0) {
            $sql .= " WHERE ";
            $index = array();
            if (isset($where[0])) {
                $sql .= self::conditions($where[0], $values, $index, $typeString);
                self::append2($insert, $index, $where);
            } else {
                $sql .= self::conditions($where, $values, $index, $typeString);
            }
        }
        return array(
            $sql,
            $values,
            $insert,
            $typeString
        );
    }
}

// index.php
class SuperSQL
{
    public $con;
    function __construct($dsn, $user, $pass)
    {
        $this->con = new Connector($dsn, $user, $pass);
    }
    function SELECT($table, $columns = array(), $where = array(), $join = null, $limit = false)
    {
        if ((gettype($join) == "integer" || gettype($join) == "string") && !$limit) {
            $limit = $join;
            $join  = null;
        }
        $d = AdvParser::SELECT($table, $columns, $where, $join, $limit);
        return $this->con->_query($d[0], $d[1], $d[2], $d[3]);
    }
    function INSERT($table, $data)
    {
        $d = AdvParser::INSERT($table, $data);
        return $this->con->_query($d[0], $d[1], $d[2], $d[3]);
    }
    function UPDATE($table, $data, $where = array())
    {
        $d = AdvParser::UPDATE($table, $data, $where);
        return $this->con->_query($d[0], $d[1], $d[2], $d[3]);
    }
    function DELETE($table, $where = array())
    {
        $d = AdvParser::DELETE($table, $where);
        return $this->con->_query($d[0], $d[1], $d[2], $d[3]);
    }
    function sSELECT($table, $columns = array(), $where = array(), $append = "")
    {
        $d = SimParser::SELECT($table, $columns, $where, $append);
        return $this->con->query($d[0], $d[1]);
    }
    function sINSERT($table, $data)
    {
        $d = SimParser::INSERT($table, $data);
        return $this->con->query($d[0], $d[1]);
    }
    function sUPDATE($table, $data, $where = array())
    {
        $d = SimParser::UPDATE($table, $data, $where);
        return $this->con->query($d[0], $d[1]);
    }
    function sDELETE($table, $where = array())
    {
        $d = SimParser::DELETE($table, $where);
        return $this->con->query($d[0], $d[1]);
    }
    function query($query, $obj = null)
    {
        return $this->con->query($query, $obj);
    }
    function close()
    {
        $this->con->close();
    }
    function dev()
    {
        $this->con->dev = true;
    }
    function getLog()
    {
        return $this->con->log;
    }
    function clearCache() 
    {
        $this->con->clearCache();
    }
    function transact($func) {
        $this->con->db->beginTransaction();
        $r = $func($this);
        if ($r === false)
            $this->con->db->rollBack();
         else 
            $this->con->db->commit();
        return $r;
    }
}
?>