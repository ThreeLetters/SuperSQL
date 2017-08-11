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
    static function getArg(&$str)
    {
        if ($str[0] === '[' && substr($str, 3, 1) === ']') {
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
    static function append(&$args, $val, $index, $values)
    {
        if (is_array($val) && $values[$index][2] < 5) {
            $len = count($val);
            for ($k = 1; $k < $len; $k++) {
                if (!isset($args[$k - 1]))
                    $args[$k - 1] = array();
                $args[$k - 1][$index] = $val[$k];
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
    
    static function append2(&$insert, $indexes, $dt, $values)
    {
        function stripArgs(&$key)
        {
            if (substr($key, -1) === ']') {
                $b   = strrpos($key, '[', -1);
                $key = substr($key, 0, $b);
            }
            $b = strrpos($key, ']', -1);
            if ($b !== false)
                $key = substr($key, $b + 1);
            
            if ($key[0] === '#') {
                    $key = substr($key, 1);
             }
            
        }
        function escape($val, $dt)
        {
        if (!isset($dt[2])) return $val;
        switch ($dt[2]) {
            case 0: //Bool
                return $val ? '1' : '0';
                break;
            case 1: // int
                return (int)$val;
                break;
            case 2: // string
                return (string)$val;
                break;
            case 3: // lob
                return $val;
                break;
            case 4: // null
                return null;
                 break;
            case 5: // json
                return json_encode($val);
                break;
            case 6: // seriealise
                return serialize($val);
                break;
        }
        }
        function recurse(&$holder, $val, $indexes, $par, $values)
        {
            foreach ($val as $k => &$v) {
                stripArgs($k);
                $k1 = $k . '#' . $par;
                
                if (isset($indexes[$k1]))
                    $d = $indexes[$k1];
                else
                    $d = $indexes[$k];
                $isArr = is_array($v) && (!isset($values[$d][2]) || $values[$d][2] < 5);
                if ($isArr) {
                    if (isset($v[0])) {
                        foreach ($v as $i => &$j) {
                            $a = $d + $i;
                            if (isset($holder[$a])) echo 'SUPERSQL WARN: Key collision: ' . $k;
                            $holder[$a] = escape($j,$values[$a]);
                        }
                    } else {
                        recurse($holder, $v, $indexes, $par . '/' . $k, $values);
                    }
                } else {
                 
                      if (isset($holder[$d])) echo 'SUPERSQL WARN: Key collision: ' . $k;
                    $holder[$d] = escape($v,$values[$d]);
                }
            }
        }
        
        $len = count($dt);
        for ($key = 1; $key < $len; $key++) {
            $val = $dt[$key];
            if (!isset($insert[$key - 1]))
                $insert[$key - 1] = array();
            
            recurse($insert[$key - 1], $val, $indexes, '', $values);
        }
        
    }
    /**
     * Puts quotes around a string
     *
     * @param {String} str - String to quote
     *
     * @returns {String}
     */
    static function quote($str)
    {
        if (strpos($str,'.') === false) {
            return '`' . $str . '`';
        } else {
        $str = explode('.', $str);
        $out = '';
        $c = count($str);
        for ($i = 0; $i < $c; $i++) {
            if ($i !== 0)
                $out .= '.';
            $out .= '`' . $str[$i] . '`';
        }
        return $out;
        }
    }
    
    /**
     * Forms the table
     *
     * @param {String|Array} table - tables(s)
     *
     * @returns {String}
     */
    static function table($table)
    {
        if (is_array($table)) {
            $sql = '';
            $c = count($table);
            for ($i = 0; $i < $c; $i++) {
                $t = self::getType($table[$i]);
                
                if ($i !== 0)
                    $sql .= ', ';
                $sql .= '`' . $table[$i] . '`';
                
                if ($t)
                    $sql .= ' AS `' . $t . '`';
            }
            return $sql;
        } else {
            return '`' . $table . '`';
        }
    }
    
    static function value($type, $value)
    {
        $var = $type ? $type : gettype($value);

        $type = \PDO::PARAM_STR;
        $dtype = 2;
    
        if ($var === 'integer' || $var === 'int' || $var === 'double' || $var === 'doub') {
            $type = \PDO::PARAM_INT;
            $dtype = 1;
            $value = (int) $value;
        } else if ($var === 'string' || $var === 'str') {
            $value = (string) $value;
            $dtype = 2;
        } else if ($var === 'boolean' || $var === 'bool') {
            $type  = \PDO::PARAM_BOOL;
            $value = $value ? '1' : '0';
            $dtype = 0;
        } else if ($var === 'null' || $var === 'NULL') {
            $dtype = 4;
            $type  = \PDO::PARAM_NULL;
            $value = null;
        } else if ($var === 'resource' || $var === 'lob') {
            $type = \PDO::PARAM_LOB;
            $dtype = 3;
        } else if ($var === 'json') {
            $dtype = 5;
            $value = json_encode($value);
        } else if ($var === 'obj') {
              $dtype = 6;
            $value = serialize($value);
        } else {
            $value = (string)$value;
            echo 'SUPERSQL WARN: Invalid type ' . $var . ' Assumed STRING';
        }
      
        return array(
            $value,
            $type,
            $dtype
        );
    }
    
    static function getType(&$str)
    {   
        $len = strlen($str);
        if ($str[$len - 1] === ']') {
            $start = strrpos($str, '[');
            
            if ($start === false) {
                return '';
            }
            $out = substr($str, $start + 1, -1);
            $str = substr($str, 0, $start);
            return $out;
        } else
            return '';
        
    }
    
    static function rmComments($str) {
        $i = strpos($str,'#');
        if ($i !== false) {
            $str = trim(substr($str,0,$i));
        }
        return $str;
    }
    /**
     * Constructs logical conditional statements
     *
     * @param {Object|Array} arr - Conditions
     * @param {Array} args
     *
     * @returns {String}
     */
    static function conditions($dt, &$values = false, &$map = false, &$index = 0)
    {
        $build = function(&$build, $dt, &$map, &$index, &$values, $join = ' AND ', $operator = ' = ', $parent = '')
        {
            $num = 0;
            $sql = '';
            
            foreach ($dt as $key => &$val) {
                if ($key[0] === '#') {
                    $raw = true;
                    $key = substr($key, 1);
                } else {
                    $raw = false;
                }
                
                $arg         = self::getArg($key);
                $arg2        = $arg ? self::getArg($key) : false;
                $useBind     = !isset($val[0]);
                $newJoin     = $join;
                $newOperator = $operator;
                $type = $raw ? false : self::getType($key);
                $column = self::quote(self::rmComments($key));
                
                switch ($arg) {
                    case '||':
                        $arg     = $arg2;
                        $newJoin = ' OR ';
                        break;
                    case '&&':
                        $arg     = $arg2;
                        $newJoin = ' AND ';
                        break;
                }
                
                switch ($arg) { // different conditionals
                    case '!=':
                        $newOperator = ' != ';
                        break;
                    case '>>':
                        $newOperator = ' > ';
                        break;
                    case '<<':
                        $newOperator = ' < ';
                        break;
                    case '>=':
                        $newOperator = ' >= ';
                        break;
                    case '<=':
                        $newOperator = ' <= ';
                        break;
                    case '~~':
                        $newOperator = ' LIKE ';
                        break; 
                    case '!~':
                        $newOperator = ' NOT LIKE ';
                        break; 
                    default:
                        if (!$useBind || $arg === '==')
                            $newOperator = ' = '; // reset
                        break;
                }
                
                if ($num !== 0)
                    $sql .= $join;
            
                
                if (is_array($val) && $type !== 'json' && $type !== 'obj') {
                    if ($useBind) {
                        $sql .= '(' . $build($build, $val, $map, $index, $values, $newJoin, $newOperator, $parent . '/' . $key) . ')';
                    } else {
                        if ($map !== false && !$raw) {
                            $map[$key]                 = $index;
                            $map[$key . '#' . $parent] = $index++;
                        }
                        foreach ($value as $k => &$v) {
                            if ($k !== 0)
                                $sql .= $newJoin;
                            $index++;
                            $sql .= $column . $newOperator;
                            if ($raw) {
                                $sql .= $v;
                            } else if ($values !== false) {
                                $sql .= '?';
                                array_push($values, self::value($type, $v));
                            } else {
                                if (is_int($v)) {
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
                            $sql .= '?';
                            array_push($values, self::value($type, $val));
                        } else {
                            if (is_int($val)) {
                                $sql .= $val;
                            } else {
                                $sql .= self::quote($val);
                            }
                        }
                        if ($map !== false) {
                            $map[$key]                 = $index;
                            $map[$key . '#' . $parent] = $index++;
                        }
                    }
                }
                 $num++;
            }
            return $sql;
        };
        
        return $build($build, $dt, $map, $index, $values);
    }
    
    static function JOIN($join, &$sql) {
        foreach ($join as $key => &$val) {
                if ($key[0] === '#') {
                    $raw = true;
                    $key = substr($key, 1);
                } else {
                    $raw = false;
                }
                
                $arg = self::getArg($key);
                switch ($arg) {
                    case '<<':
                        
                        $sql .= ' RIGHT JOIN ';
                        break;
                    case '>>':
                        
                        $sql .= ' LEFT JOIN ';
                        break;
                    case '<>':
                        
                        $sql .= ' FULL JOIN ';
                        break;
                    default: // inner join
                        
                        $sql .= ' JOIN ';
                        break;
                }
                
                $sql .= '`' . $key . '` ON ';
                
                if ($raw) {
                    $sql .= 'val';
                } else {
                    $sql .= self::conditions($val);
                }
            }
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
        $sql = 'SELECT ';
        
        
        $values = array();
        $insert = array();
        $outTypes = null;
        
        if (!isset($columns[0])) { // none
            $sql .= '*';
        } else { // some
            $len = count($columns);
            $req  = 0;
            $into = '';
            if ($columns[0] === 'DISTINCT') {
                $req = 1;
                $sql .= 'DISTINCT ';
            } else if (substr($columns[0], 0, 11) === 'INSERT INTO') {
                $req = 1;
                $sql = $columns[0] . ' ' . $sql;
            } else if (substr($columns[0], 0, 4) === 'INTO') {
                $req  = 1;
                $into = ' ' . $columns[0] . ' ';
            }
            
            if ($len > $req) { // has var
                
                for ($i = $req; isset($columns[$i]); $i++) {
                    $b = self::getType($columns[$i]);
                    $t = $b ? self::getType($columns[$i]) : false;
                    if (!$t && $b) {
                        if (!($b === 'int' || $b === 'string' || $b === 'json' || $b === 'obj' || $b === 'bool')) {
                            $t = $b;
                            $b = false;   
                        }
                    }
                    if ($b) {
                        if (!$outTypes) $outTypes = array();
                        if ($t) {
                      $outTypes[$t] = $b;
                        } else {
                        $outTypes[$columns[$i]] = $b;
                        }
                    }
                    if ($i > $req) {
                        $sql .= ', ';
                    }
                    $sql .= self::quote($columns[$i]);
                    if ($t)
                        $sql .= ' AS `' . $t . '`';
                }
                
            } else
                $sql .= '*';
            
            $sql .= $into;
        }
        $sql .= ' FROM ' . self::table($table);
        
        if ($join) {
            self::JOIN($join,$sql);
        }
        if (!empty($where)) {
            $sql .= ' WHERE ';
            $index = array();
            if (isset($where[0])) {
                $sql .= self::conditions($where[0], $values, $index);
                self::append2($insert, $index, $where, $values);
            } else {
                $sql .= self::conditions($where, $values, $index);
            }
        }
        
        if ($limit) {
            if (is_int($limit)) {
                 $sql .= ' LIMIT ' . $limit; 
            } else if (is_string($limit)) {
                 $sql .= ' ' . $limit; 
            }
        }
           
        
        return array(
            $sql,
            $values,
            $insert,
            $outTypes
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
        $sql        = 'INSERT INTO ' . self::table($table) . ' (';
        $values     = array();
        $insert     = array();
        $append     = '';
        
        $i       = 0;
        $b       = 0;
        $indexes = array();
        $multi   = isset($data[0]);
        $dt      = $multi ? $data[0] : $data;
        foreach ($dt as $key => &$val) {
            if ($key[0] === '#') {
                $raw = true;
                $key = substr($key, 1);
            } else {
                $raw = false;
            }
            if ($b !== 0) {
                $sql .= ', ';
                $append .= ', ';
            }
            $type = self::getType($key);
            
            $sql .= '`' . self::rmComments($key) . '`';
            if ($raw) {
                $append .= $val;
            } else {
                $append .= '?';
                array_push($values, self::value($type, $val));
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
        
        $sql .= ') VALUES (' . $append . ')';
        
        return array(
            $sql,
            $values,
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
        $sql        = 'UPDATE ' . self::table($table) . ' SET ';
        $values     = array();
        $insert     = array();
        $i          = 0;
        $b          = 0;
        $indexes    = array();
        $multi      = isset($data[0]);
        $dt         = $multi ? $data[0] : $data;
        foreach ($dt as $key => &$val) {
            if ($key[0] === '#') {
                $raw = true;
                $key = substr($key, 1);
            } else {
                $raw = false;
            }
            if ($b !== 0) {
                $sql .= ', ';
            }
            if ($raw) {
                $sql .= '`' . $key . '` = ' . $val;
            } else {
                $arg = self::getArg($key);
                $sql .= '`' . $key . '` = ';
                
                switch ($arg) {
                    case '+=':
                        $sql .= '`' . $key . '` + ?';
                        break;
                    case '-=':
                        $sql .= '`' . $key . '` - ?';
                        break;
                    case '/=':
                        $sql .= '`' . $key . '` / ?';
                        break;
                    case '*=':
                        $sql .= '`' . $key . '` * ?';
                        break;
                    default:
                        $sql .= '?';
                        break;
                }
                $type = self::getType($key);
                array_push($values, self::value($type, $val));
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
        
        if (!empty($where)) {
            $sql .= ' WHERE ';
            $index = array();
            if (isset($where[0])) {
                $sql .= self::conditions($where[0], $values, $index, $i);
                self::append2($insert, $index, $where, $values);
            } else {
                $sql .= self::conditions($where, $values, $index, $i);
            }
        }
        return array(
            $sql,
            $values,
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
        $sql        = 'DELETE FROM ' . self::table($table);
        $values     = array();
        $insert     = array();
        
        if (!empty($where)) {
            $sql .= ' WHERE ';
            $index = array();
            if (isset($where[0])) {
                $sql .= self::conditions($where[0], $values, $index);
                self::append2($insert, $index, $where, $values);
            } else {
                $sql .= self::conditions($where, $values, $index);
            }
        }
        
        return array(
            $sql,
            $values,
            $insert
        );
    }
    
}
// BUILD BETWEEN

?>
