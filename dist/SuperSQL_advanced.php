<?php
/*
 Author: Andrews54757
 License: MIT (https://github.com/ThreeLetters/SuperSQL/blob/master/LICENSE)
 Source: https://github.com/ThreeLetters/SQL-Library
 Build: v1.0.0
 Built on: 09/08/2017
*/

// lib/connector/index.php
class Response
{
    public $result;
    public $affected;
    public $ind;
    public $error;
    public $errorData;
    function __construct($data, $error, $outtypes)
    {
        $this->error = !$error;
        if (!$error) {
            $this->errorData = $data->errorInfo();
        } else {
            $d = $data->fetchAll();
            $this->result = $d;
            if (count($outtypes) != 0) {
                foreach ($d as $i => $row) {
                    foreach ($outtypes as $col => $dt) {
                        if (isset($row[$col])) {
                            switch ($dt) {
                                case "int":
                                    $this->result[$i][$col] = (int)$row[$col];
                                    break;
                                case "string":
                                    $this->result[$i][$col] = (string)$row[$col];
                                    break;
                                case "bool":
                                    $this->result[$i][$col] = $row[$col] ? true : false;
                                    break;
                                case "json":
                                    $this->result[$i][$col] = json_decode($row[$col]);
                                    break;
                                case "obj":
                                    $this->result[$i][$col] = unserialize($row[$col]);
                                    break;
                            }
                        }
                    }
                }
            }
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
    function query($query,$obj = null,$outtypes = array())
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
        return new Response($q,$e,$outtypes);
    }
    function _query($sql, $values, $insert, $typeString, $outtypes = array())
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
            return new Response($q, $e, $outtypes);
        } else { 
            $responses = array();
            $e = $q->execute();
            array_push($responses,new Response($q, $e, $outtypes));
            foreach ($insert as $key => $value) {
                foreach ($value as $k => $val) {
                    $v[$k][0] = $val;
                }
                $e = $q->execute();
                array_push($responses, new Response($q, $e, $outtypes));
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
    private static function append(&$args, $val, $index, $values)
    {
        if (gettype($val) == "array" && $values[$index][2] < 5) {
            $len = count($val);
            for ($k = 1; $k < $len; $k++) {
                if (!isset($args[$k]))
                    $args[$k] = array();
                $args[$k][$index] = $val[$k];
            }
        }
    }
    private static function append2(&$insert, $indexes, $dt, $values)
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
            if (substr($key, 0, 1) === "#") {
                    $key = substr($key, 1);
             }
        }
        function escape($val, $dt)
        {
        if (!isset($dt[2])) return $val;
        switch ($dt[2]) {
            case 0: 
                return $val ? "1" : "0";
                break;
            case 1: 
                return (int)$val;
                break;
            case 2: 
                return (string)$val;
                break;
            case 3: 
                return $val;
                break;
            case 4: 
                return null;
                 break;
            case 5: 
                return json_encode($val);
                break;
            case 6: 
                return serialize($val);
                break;
        }
        }
        function recurse(&$holder, $val, $indexes, $par, $values)
        {
            foreach ($val as $k => $v) {
                stripArgs($k);
                $k1 = $k . "#" . $par;
                if (isset($indexes[$k1]))
                    $d = $indexes[$k1];
                else
                    $d = $indexes[$k];
                $isArr = gettype($v) == "array" && (!isset($values[$d][2]) || $values[$d][2] < 5);
                if ($isArr) {
                    if (isset($v[0])) {
                        foreach ($v as $i => $j) {
                            $a = $d + $i;
                            if (isset($holder[$a])) echo "SUPERSQL WARN: Key collision: " . $k;
                            $holder[$a] = escape($j,$values[$a]);
                        }
                    } else {
                        recurse($holder, $v, $indexes, $par . "/" . $k, $values);
                    }
                } else {
                      if (isset($holder[$d])) echo "SUPERSQL WARN: Key collision: " . $k;
                    $holder[$d] = escape($v,$values[$d]);
                }
            }
        }
        $len = count($dt);
        for ($key = 1; $key < $len; $key++) {
            $val = $dt[$key];
            if (!isset($insert[$key]))
                $insert[$key] = array();
            recurse($insert[$key], $val, $indexes, "", $values);
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
        $type = \PDO::PARAM_STR;
        $dtype = 2;
        if ($var == "boolean" || $var == "bool") {
            $type  = \PDO::PARAM_BOOL;
            $value = $value ? "1" : "0";
            $dtype = 0;
            $typeString .= "b";
        } else if ($var == "integer" || $var == "int" || $var == "double" || $var == "doub") {
            $typeString .= "i";
            $dtype = 1;
            $value = (int) $value;
        } else if ($var == "string" || $var == "str") {
            $type = \PDO::PARAM_STR;
            $value = (string) $value;
            $dtype = 2;
            $typeString .= "s";
        } else if ($var == "resource" || $var == "lob") {
            $type = \PDO::PARAM_LOB;
            $dtype = 3;
            $typeString .= "l";
        } else if ($var == "null") {
            $dtype = 4;
            $type  = \PDO::PARAM_NULL;
            $value = null;
            $typeString .= "n";
        } else if ($var == "json") {
            $dtype = 5;
            $type = \PDO::PARAM_STR;
            $value = json_encode($value);
        } else if ($var == "obj") {
              $dtype = 6;
            $type = \PDO::PARAM_STR;
            $value = serialize($value);
        } else {
            $value = (string)$value;
            echo "SUPERSQL WARN: Invalid type " . $var . " Assumed STRING";
        }
        return array(
            $value,
            $type,
            $dtype
        );
    }
    private static function getType(&$str)
    {   
        if (substr($str, -1) == "]") {
            $start = strrpos($str, "[");
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
                $type = $raw ? false : self::getType($key);
                $column = self::quote(self::rmComments($key));
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
                if ($valType == "array" && $type != "json" && $type != "obj") {
                    if ($useBind) {
                        $sql .= "(" . $build($build, $val, $map, $index, $values, $newJoin, $newOperator, $parent . "/" . $key) . ")";
                    } else {
                        if ($map !== false && !$raw) {
                            $map[$key]                 = $index;
                            $map[$key . "#" . $parent] = $index++;
                        }
                        foreach ($value as $k => $v) {
                            if ($k != 0)
                                $sql .= $newJoin;
                            $index++;
                            $sql .= $column . $newOperator;
                            if ($raw) {
                                $sql .= $v;
                            } else if ($values !== false) {
                                $sql .= "?";
                                array_push($values, self::value($type, $v, $typeString));
                            } else {
                                if (gettype($v) == "integer") {
                                    $sql .= $v;
                                } else {
                                    $sql .= self::quote($v);
                                }
                            }
                        }
                    }
                } else {
                    $sql .= $column . $newOperator;
                    if ($raw) {
                          $sql .= $val;
                    } else {
                        if ($values !== false) {
                            $sql .= "?";
                            array_push($values, self::value($type, $val, $typeString));
                        } else {
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
        $outTypes = array();
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
                    $b = self::getType($columns[$i]);
                    $t = $b ? self::getType($columns[$i]) : false;
                    if (!$t && $b) {
                        if (!($b == "int" || $b == "string" || $b == "json" || $b == "obj" || $b == "bool")) {
                            $t = $b;
                            $b = false;   
                        }
                    }
                    if ($b) {
                        if ($t) {
                      $outTypes[$t] = $b;
                        } else {
                        $outTypes[$columns[$i]] = $b;
                        }
                    }
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
                self::append2($insert, $index, $where, $values);
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
            $typeString,
            $outTypes
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
                    self::append($insert, $val, $i++, $values);
                }
            }
            $b++;
        }
        if ($multi)
            self::append2($insert, $indexes, $data, $values);
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
                    self::append($insert, $val, $i++, $values);
                }
            }
            $b++;
        }
        if ($multi)
            self::append2($insert, $indexes, $data, $values);
        if (count($where) != 0) {
            $sql .= " WHERE ";
            $index = array();
            if (isset($where[0])) {
                $sql .= self::conditions($where[0], $values, $index, $typeString, $i);
                self::append2($insert, $index, $where, $values);
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
                self::append2($insert, $index, $where, $values);
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
        return $this->con->_query($d[0], $d[1], $d[2], $d[3], $d[4]);
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