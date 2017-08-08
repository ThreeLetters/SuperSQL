<?php

namespace SuperSQL;

/*
MIT License

Copyright (c) 2017 Andrew S (Andrews54757_at_gmail.com)

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
Source: https://github.com/ThreeLetters/SuperSQL
*/

// BUILD BETWEEN
class AdvParser
{
    /**
     * Reads argument data from a string
     *
     * @param {String} str - String to read from
     *
     * @returns {String|Boolean}
     */
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
    
    /**
     * Appends value(s) to arguments
     *
     * @param {&Array} args - Arguments to append to
     * @param {String|Int|Boolean|Array} val - Value(s) to append
     */
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
    
    /**
     * Appends values to arguments
     *
     * @param {&Array} insert - Arguments to append to
     * @param {Object} indexes - Indexes of keys
     * @param {Array} dt - Values to append
     */
    
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
    /**
     * Puts quotes around a string
     *
     * @param {String} str - String to quote
     *
     * @returns {String}
     */
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
    
    /**
     * Forms the table
     *
     * @param {String|Array} table - tables(s)
     *
     * @returns {String}
     */
    private static function table($table)
    {
        if (gettype($table) == "array") {
            $sql = "";
            for ($i = 0; $i < count($table); $i++) {
                $t = self::getType($table[i]);
                
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
        } else if ($var == "string" || $var == "str") {
            $type = \PDO::PARAM_STR;
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
    /**
     * Constructs logical conditional statements
     *
     * @param {Object|Array} arr - Conditions
     * @param {Array} args
     *
     * @returns {String}
     */
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
                
                switch ($arg) { // different conditionals
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
                            $newOperator = " = "; // reset
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
    
    /**
     * Constructs SQL commands (SELECT)
     *
     * @param {String} table - SQL Table
     * @param {Array} columns - Columns to return
     * @param {Object|Array} where - Where clause
     * @param {Object|null} join - Join clause 
     * @param {Int} limit - Limit clause
     * 
     * @returns {Array}
     */
    static function SELECT($table, $columns, $where, $join, $limit)
    {
        $sql = "SELECT ";
        
        $len = count($columns);
        
        $values = array();
        $insert = array();
        
        if ($len == 0) { // none
            $sql .= "*";
        } else { // some
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
            
            if ($len > $req) { // has var
                
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
                    default: // inner join
                        
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
        
        if ($limit)
            $sql .= " LIMIT " . $limit;
        
        return array(
            $sql,
            $values,
            $insert,
            $typeString
        );
        
    }
    
    /**
     * Constructs SQL commands (INSERT INTO)
     *
     * @param {String} table - SQL Table
     * @param {Object|Array} data - Data to insert
     *
     * @returns {Array}
     */
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
    
    /**
     * Constructs SQL commands (UPDATE)
     *
     * @param {String} table - SQL Table
     * @param {Object|Array} data - Data to update
     * @param {Object|Array} where - Where clause
     *
     * @returns {Array}
     */
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
    
    /**
     * Constructs SQL commands (DELETE)
     *
     * @param {String} table - SQL Table
     * @param {Object|Array} where - Where clause
     *
     * @returns {Array}
     */
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
// BUILD BETWEEN

?>
