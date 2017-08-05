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
 Built on: 05/08/2017
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
    function getAffected() {
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
    public $queries = [];
    public $db;
    public $log = [];
    public $dev = false;
    function __construct($dsn, $user, $pass)
    {
        $this->db = new \PDO($dsn, $user, $pass);
        $this->log = array();
    }
    function query($query)
    {
        $q = $this->db->prepare($query);
        $q->execute();
        if ($this->dev) array_push($this->log,[$query]);
        return new Response($q);
    }
    function _query($sql, $insert)
    {
         n_encode(array($sql,$insert));
         return;
        if (isset($this->queries[$sql])) { ries[$sql];
            if ($this->dev) array_push($this->log,["fromcache",$sql,$insert]);
        } else {
            $q             = $this->db->prepare($sql);
            $this->queries[$sql] = $q;
            if ($this->dev) array_push($this->log,[$sql,$insert]);
        }
        if (count($insert) == 1) { ngle query
            $e = $q->execute($insert[0]);
            return new Response($q, $e);
        } else { ry
            $responses = array();
            foreach ($insert as $key => $value) {
                $e = $q->execute($insert[0]);
                array_push($responses, new Response($q, $e));
            }
            return responses;
        }
    }
    function close()
    {
        $this->db = null;
        $this->queries = null;
    }
    function clear()
    {
        $this->queries = [];   
    }
}

// lib/parser/Simple.php
class SimpleParser {
    public static function WHERE($where, &$sql, &$insert) {
         if (count($where) != 0) {
        $sql .= " WHERE ";
        $i = 0;
        foreach ($where as $key => $value) {
            if ($i != 0) {
                $sql .= " AND ";
            }
            $sql .= "`" . $key . "` = ?";
            array_push($insert[0],$value);
            $i++;
        }
        }
    }
    public static function SELECT($table,$columns,$where,$append) {
        $sql = "SELECT ";
        $insert = array(array());
        $len = count($columns);
        if ($len == 0) { none
            $sql .= "*";
        } else { r ($i = 0; $i < $len; $i++) {
                    if ($i != 0) {
                        $sql .= ", ";
                    }
                    $sql .= "`" . $columns[$i] . "`";
                }
        }
        $sql .= "FROM `" . $table . "`";
        self::WHERE($where,$sql,$insert);
        $sql .= " " . $append;
        return array($sql,$insert);
    }
    public static function INSERT($table,$data) {
        $sql = "INSERT INTO `" . $table . "` (";
        $add = ") VALUES (";
        $insert = array(array());
        $i = 0;
        foreach ($data as $key => $value) {
            if ($i != 0) {
                $sql .= ", ";
                $add .= ", ";
            }
            $sql .= "`" . $key . "`";
            $add .= "?";
            array_push($insert[0],$value);
            $i++;
        }
        $sql .= $add;
        return array($sql,$insert);
    }
    public static function UPDATE($table,$data,$where) {
        $sql = "UPDATE `" . $table . "` SET ";
        $insert = array(array());
        $i = 0;
        foreach ($data as $key => $value) {
            if ($i != 0) {
                $sql .= ", ";
            }
           $sql .= "`" . $key . "` = ?"; 
           array_push($insert[0],$value);
               $i++;
        }
        self::WHERE($where,$sql,$insert);
        return array($sql,$insert);
    }
    public static function DELETE($table,$where) {
        $sql = "DELETE FROM `" . $table . "`";
        $insert = array(array());
        self::WHERE($where,$sql,$insert);
        return array($sql,$insert);
    }
}

// lib/parser/Advanced.php
class AdvancedParser
{
    private static function parseArg(&$str)
    {
        if (substr($str, 0, 1) == "[") {
            $out = substr($str, 1, 3);
             $str = substr($str,4);
            return $out;
        } else {
            return false;
        }
    }
    private static function parseArgs(&$str)
    {
        $arr = array();
        for ($i = 0; $i < 5; $i++) {
        if (substr($str, 0, 1) == "[") {
          array_push($arr,substr($str, 1, 3));
         $str = substr($str,4);
        } else {
            return $arr;
        }
        }
    }
    private static function append(&$args, $val)
    {
        $type = gettype($val);
        if ($type == "array") {
            foreach ($val as $k => $v) {
                $k = (int)$k;
                if (!isset($args[$k]))
                    $args[$k] = array_slice($args[0], 0);
                if (!$first)
                    $first = $v;
            }
            foreach ($args as $k => $v) {
                $k = (int)$k;
                if (isset($val[$k])) {
                    array_push($args[$k], $val[$k]);
                } else {
                    array_push($args[$k], $first);
                }
            }
        } else {
            foreach ($args as $k => $v) {
                array_push($args[$k], $val);
            }
        }
    }
    private static function append2(&$insert, $indexes, $dt)
    {
        function recurse(&$holder,$val,$indexes,$par,$lvl) {
            foreach ($val as $k => $v) {
                if (gettype($v) == "array") {
                    $a = substr($k,0,4);
                    $b = strrpos($k,"]", -1);
                     if ($b != false) {
                     $k = substr($k,$b+1);
                     }
                    if ($a != "[||]" && $a != "[&&]") {
                    $d = $indexes[$k . "#" . $lvl . "." . $par . "*"];
                        if (!$d) $d = $indexes[$k . "*"];
                     foreach ($v as $i => $j) {
                         $holder[$d + $i] = $j;
                     }   
                    } else {
                    recurse($holder,$v,$indexes,$k, $lvl + 1);
                    }
                } else {
                     $b = strrpos($k,"]", -1);
                     if ($b != false) {
                     $k = substr($k,$b+1);
                     }
                    $d = $indexes[$k . "#" . $lvl . "." . $par];
                    if (!$d) $d = $indexes[$k];
                $holder[$d] = $v;
                }
            }
        }
        $last = false;
        foreach ($dt as $key => $val) {
            if (!isset($insert[$key]))
                $insert[$key] = array();
            $holder = $last ? array_slice($last, 0) : array();
            recurse($holder,$val,$indexes,"",0);
            if (!$last)
                $last = $holder;
            $c = count($holder);
            for ($i = 0; $i < $c; $i++) {
                array_push($insert[$key], $holder[$i]);
            }
        }
    }
    private static function conditions($arr, &$args, $quotes = true)
    {
        $cond = function(&$cond,&$arr,&$args,$quotes,$statement,$default,&$indexes,&$i,$lvl,$parent,$append = false) {
            $b = 0;
            foreach ($arr as $key => $value) {
                $arg = self::parseArg($key);
                $type = gettype($value);
                $s = $statement;
                $o = $default;
                $useBind = false;
                if ($type == "array") {
                switch ($arg) {
                    case "&&]":
                        $s = " AND ";
                        $arg = self::parseArg($key);
                        $useBind = true;
                        break;
                    case "||]":
                        $s = " OR ";
                        $arg = self::parseArg($key);
                        $useBind = true;
                         break;
                    }
                }
                    switch ($arg) {
                case ">>]":
                    $o = " > ?";
                    break;
                case "<<]":
                    $o = " < ?";
                    break;
                case ">=]":
                    $o = " >= ?";
                    break;
                case "<=]":
                    $o = " <= ?";
                    break;
                default:      
                if ($useBind) $o = " = ?";
                    break;
                    }
                if ($b != 0) $sql .= $statement;
                if ($type == "array") {
                  if ($useBind) {
                    $sql .= "(" .$cond($cond,$value,$args,$quotes,$s,$o,$indexes,$i,$lvl + 1,$key,$append) . ")";
                  } else {
                       $indexes[$key . "*"] = $i;
                       $indexes[$key . "#" . $lvl . "." . $parent . "*"] = $i;
                      foreach ($value as $k => $v) {
                        if ($k != 0) $sql .= $statement;
                         if ($quotes) {
                          $sql .= "`" . $key . "`" . $o;
                         } else {
                            $sql .= $key . $o;
                         }
                          $i++;   
                          if ($append) {
                               array_push($args[0],$v); 
                          }
                      }
                  }
                } else {
                 if ($quotes) {
                  $sql .= "`" . $key . "`" . $o;
                 } else {
                   $sql .= $key . $o;
                 }
                    if ($append) {
                      array_push($args[0],$value);
                    } 
                     $indexes[$key] = $i;
                      $indexes[$key . "#" . $lvl . "." . $parent] = $i++;
                }
                $b++;
                }
              return $sql;
        };
$indexes = array();
     $i = 0;
      if (isset($arr[0])) {
           $sql = $cond($cond,$arr[0],$args,$quotes," AND ", " = ?",$indexes,$i,0,false);
           self::append2($args,$indexes,$arr);
      } else {
            $sql = $cond($cond,$arr,$args,$quotes," AND ", " = ?",$indexes,$i,0,false,true);
      }
        return $sql;
    }
    static function SELECT($table, $columns, $where, $join, $limit)
    {
        $sql = "SELECT ";
        $len = count($columns);
        $insert = array(
            array()
        );
        if ($len == 0) { none
            $sql .= "*";
        } else { req = 0;
            $into = "";
            if ($columns[0] == "DISTINCT") {
                $i = 1;
                $req = 1;
                $sql .= "DISTINCT ";
            } else if (substr($columns[0],0,11) == "INSERT INTO") {
                $i = 1;
                $req = 1;
                $sql = $columns[0] . " " . $sql;
            } else if (substr($columns[0],0,4) == "INTO") {
                $i = 1;
                $req = 1;
                $into = " " . $columns[0] . " ";
            }
            if ($len > $req) { r
                for (; $i < $len; $i++) {
                    if ($i > $req) {
                        $sql .= ", ";
                    }
                    $sql .= "`" . $columns[$i] . "`";
                }
            } else
                $sql .= "*";
            $sql .= $into;
        }
        $sql .= " FROM `" . $table . "`";
        if ($join) {
            foreach ($join as $key => $val) {
                $arg = self::parseArg($key);
                switch ($arg) {
                    case "<<]":
                        $sql .= " RIGHT JOIN ";
                        break;
                    case ">>]":
                        $sql .= " LEFT JOIN ";
                        break;
                    case "<>]":
                        $sql .= " FULL JOIN ";
                        break;
                    default: nner join
                        $sql .= " JOIN ";
                        break;
                }
                $sql .= "`" . $key . "` ON ";
                $c = self::conditions($val, $insert,false);
                $sql .= $c;
            }
        }
        if (count($where) != 0) {
            $sql .= " WHERE ";
            $c = self::conditions($where, $insert);
            $sql .= $c;
        }
       if ($limit) $sql .= " LIMIT " . $limit;
        return array(
            $sql,
            $insert
        );
    }
    static function INSERT($table, $data)
    {
        $sql    = "INSERT INTO `" . $table . "` (";
        $insert = array(
            array()
        );
        $values = "";
        $i = 0;
        if (isset($data[0])) {
            $indexes = array();
            foreach ($data[0] as $key => $val) {
                if ($i != 0) {
                    $sql .= ", ";
                    $values .= ", ";
                }
                $sql .= "`" . $key . "`";
                $values .= "?";
                $indexes[$key] = $i++;
            }
            self::append2($insert, $indexes, $data);
        } else {
            foreach ($data as $key => $val) {
                if ($i != 0) {
                    $sql .= ", ";
                    $values .= ", ";
                }
                $sql .= "`" . $key . "`";
                $values .= "?";
                $i++;
                self::append($insert, $val);
            }
        }
        $sql .= ") VALUES (" . $values . ")";
        return array(
            $sql,
            $insert
        );
    }
    static function UPDATE($table, $data, $where)
    {
        $sql    = "UPDATE `" . $table . "` SET ";
        $insert = array(
            array()
        );
        $i = 0;
        if (isset($data[0])) {
            $indexes = array();
            foreach ($data[0] as $key => $val) {
                if ($i != 0) {
                    $sql .= ", ";
                }
                $indexes[$key] = $i++;
                $sql .= "`" . $key . "` = ?";
            }
            self::append2($insert, $indexes, $data);
        } else {
            foreach ($data as $key => $val) {
                if ($i != 0) {
                    $sql .= ", ";
                }
                $i++;
                $sql .= "`" . $key . "` = ?";
                self::append($insert, $val);
            }
        }
        if (count($where) != 0) {
            $sql .= " WHERE ";
            $c = self::conditions($where, $insert);
            $sql .= $c;
        }
        return array(
            $sql,
            $insert
        );
    }
    static function DELETE($table, $where)
    {
        $sql    = "DELETE FROM `" . $table . "`";
        $insert = array(
            array()
        );
        if (count($where) != 0) {
            $sql .= " WHERE ";
            $c = self::conditions($where, $insert);
            $sql .= $c;
        }
        return array(
            $sql,
            $insert
        );
    }
}

// index.php
class SuperSQL
{
    public $connector;
    function __construct($dsn, $user, $pass)
    {
        $this->connector = new Connector($dsn, $user, $pass);
    }
    function SELECT($table, $columns, $where, $join = null, $limit = false)
    {
     if (gettype($join) == "integer") {
            $limit = $join;
            $join = null;
        }
        $d = Parser::SELECT($table, $columns, $where, $join, $limit);
        return $this->connector->_query($d[0], $d[1]);
    }
    function INSERT($table, $data)
    {
        $d = Parser::INSERT($table, $data);
        return $this->connector->_query($d[0], $d[1]);
    }
    function UPDATE($table, $data, $where)
    {
        $d = Parser::UPDATE($table, $data, $where);
        return $this->connector->_query($d[0], $d[1]);
    }
    function DELETE($table, $where)
    {
        $d = Parser::DELETE($table, $where);
        return $this->connector->_query($d[0], $d[1]);
    }
    function sSELECT($table, $columns, $where, $append = "")
    {
        $d = Simple::SELECT($table, $columns, $where, $append);
        return $this->connector->_query($d[0], $d[1]);
    }
    function sINSERT($table, $data)
    {
        $d = Simple::INSERT($table, $data);
        return $this->connector->_query($d[0], $d[1]);
    }
    function sUPDATE($table, $data, $where)
    {
        $d = Simple::UPDATE($table, $data, $where);
        return $this->connector->_query($d[0], $d[1]);
    }
    function sDELETE($table, $where)
    {
        $d = Simple::DELETE($table, $where);
        return $this->connector->_query($d[0], $d[1]);
    }
    function query($query) {
        return $this->connector->query($query);
    }
    function close() {
        $this->connector->close();
    }
    function dev() {
        $this->connector->dev = true;
    }
    function getLog() {
        return $this->connector->log;
    }
}
?>