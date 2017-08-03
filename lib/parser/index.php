<?php

namespace SQLib;

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
 Source: https://github.com/Andrews54757/SQL-Library
*/


class Parser
{
   /**
    * Reads argument data from a string
    *
    * @param {String} str - String to read from
    *
    * @returns {String|Boolean}
    */
    private static function parseArg($str)
    {
        if (substr($str, 0, 1) == "[") {
            return substr($str, 1, 3);
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
    private static function append(&$args, $val)
    {
        $type = gettype($val);
        if ($type == "array") {
            foreach ($val as $k => $v) {
                $k = (int) $k;
                if (!isset($args[$k]))
                    $args[$k] = array_slice($args[0], 0);
                if (!$first)
                    $first = $v;
            }
            foreach ($args as $k => $v) {
                $k = (int) $k;
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
    
   /**
    * Appends values to arguments
    *
    * @param {&Array} insert - Arguments to append to
    * @param {Object} indexes - Indexes of keys
    * @param {Array} dt - Values to append
    */
    
    private static function append2(&$insert, $indexes, $dt)
    {
        $last = false;
        foreach ($dt as $key => $val) {
            if (!isset($insert[$key]))
                $insert[$key] = array();
            $holder = $last ? array_slice($last, 0) : array();
            
            foreach ($val as $k => $v) {
                
                $holder[$indexes[$k]] = $v;
            }
            
            if (!$last)
                $last = $holder;
            $c = count($holder);
            
            for ($i = 0; $i < $c; $i++) {
                array_push($insert[$key], $holder[$i]);
            }
        }
        
    }
    
   /**
    * Constructs logical conditional statements
    *
    * @param {Object|Array} arr - Conditions
    * @param {Array} args
    *
    * @returns {String}
    */
    private static function conditions($arr, &$args)
    {
        
        $cond = function($i, $key, &$sql, &$indexes)
        {
            $arg = self::parseArg($key);
            switch ($arg) {
                case "||]":
                    $key = substr($key, 4);
                    $arg = self::parseArg($key);
                    $sql .= " OR ";
                    break;
                default:
                    if ($arg == "&&]") {
                        $key = substr($key, 4);
                        $arg = self::parseArg($key);
                        $sql .= " AND ";
                    } else if ($i != 0) {
                        $sql .= " AND ";
                    }
                    
                    break;
            }
            switch ($arg) {
                case ">>]":
                    $key = substr($key, 4);
                    $sql .= "`" . $key . "` > ?";
                    break;
                case "<<]":
                    $key = substr($key, 4);
                    $sql .= "`" . $key . "` < ?";
                    break;
                case ">=]":
                    $key = substr($key, 4);
                    $sql .= "`" . $key . "` >= ?";
                    break;
                case "<=]":
                    $key = substr($key, 4);
                    $sql .= "`" . $key . "` <= ?";
                    break;
                default:
                    if ($arg == "==]")
                        $key = substr($key, 4);
                    $sql .= "`" . $key . "` = ?";
                    break;
            }
            $indexes[$key] = $i;
        };
        
        $sql     = "";
        // $args = array(array());
        $i       = 0;
        $indexes = array();
        
        if (isset($arr[0])) {
            foreach ($arr[0] as $key => $val) {
                $cond($i++, $key, $sql, $indexes);
            }
            self::append2($args, $indexes, $arr);
        } else {
            foreach ($arr as $key => $val) {
                $cond($i++, $key, $sql, $indexes);
                self::append($args, $val);
            }
        }
        return $sql;
        
    }
    
   /**
    * Constructs SQL commands (SELECT)
    *
    * @param {String} table - SQL Table
    * @param {Array} columns - Columns to return
    * @param {Object|Array} where - Where clause
    * @param {Object|null} join - Join clause 
    * 
    * @returns {Array}
    */
    static function SELECT($table, $columns, $where, $join)
    {
        
        $sql = "SELECT ";
        
        $len = count($columns);
        
        $insert = array(
            array()
        );
        
        
        if ($len == 0) { // none
            $sql .= "*";
        } else { // some
            if ($columns[0] == "DISTINCT") {
                $dis = true;
                $sql .= "DISTINCT ";
            }
            
            if (!$dis || $len != 2) { // has var
                
                for ($i = $dis ? 1 : 0; $i < $len; $i++) {
                    
                    if ($i != 0 && (!$dis || $i != 1)) {
                        $sql .= ", ";
                    }
                    $sql .= "`" . $columns[$i] . "`";
                }
                
            } else
                $sql .= "*";
        }
        
        $sql .= " FROM `" . $table . "`";
        
        if ($join) {
            foreach ($join as $key => $val) {
                $arg = self::parseArg($key);
                switch ($arg) {
                    case "<<]":
                        $key = substr($key, 4);
                        $sql .= " RIGHT JOIN ";
                        break;
                    case ">>]":
                        $key = substr($key, 4);
                        $sql .= " LEFT JOIN ";
                        break;
                    case "<>]":
                        $key = substr($key, 4);
                        $sql .= " FULL JOIN ";
                        break;
                    default: // inner join
                        
                        if ($arg == "><]")
                            $key = substr($key, 4);
                        $sql .= " JOIN ";
                        break;
                }
                
                $sql .= "`" . $key . "` ON ";
                
                $c = self::conditions($val, $insert);
                $sql .= $c;
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


?>
