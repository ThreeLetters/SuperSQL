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
class SimParser
{
    public static function escape($value) {
        
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
    public static function WHERE($where, &$sql)
    {
        if (count($where) != 0) {
            $sql .= " WHERE ";
            $i = 0;
            foreach ($where as $key => $value) {
                
                if ($i != 0) {
                    $sql .= " AND ";
                }
                $sql .= "`" . $key . "` = " . $self::escape($value);
                $i++;
            }
        }
    }
    public static function SELECT($table, $columns, $where, $append)
    {
        $sql    = "SELECT ";
        $len    = count($columns);
        if ($len == 0) { // none
            $sql .= "*";
        } else { // some
            for ($i = 0; $i < $len; $i++) {
                if ($i != 0) {
                    $sql .= ", ";
                }
                $sql .= "`" . $columns[$i] . "`";
            }
        }
        
        $sql .= "FROM `" . $table . "`";
        
        self::WHERE($where, $sql);
        
        $sql .= " " . $append;
        
        return $sql;
    }
    public static function INSERT($table, $data)
    {
        $sql    = "INSERT INTO `" . $table . "` (";
        $add    = ") VALUES (";
        
        $i = 0;
        
        foreach ($data as $key => $value) {
            
            if ($i != 0) {
                $sql .= ", ";
                $add .= ", ";
            }
            $sql .= "`" . $key . "`";
            $add .= self::escape($value);
            $i++;
        }
        
        $sql .= $add;
        
        return $sql;
    }
    public static function UPDATE($table, $data, $where)
    {
        $sql    = "UPDATE `" . $table . "` SET ";
        $i = 0;
        foreach ($data as $key => $value) {
            if ($i != 0) {
                $sql .= ", ";
            }
            $sql .= "`" . $key . "` = " . self::escape($value);
            $i++;
        }
        
        self::WHERE($where, $sql);
        return $sql;
    }
    public static function DELETE($table, $where)
    {
        $sql    = "DELETE FROM `" . $table . "`";
        self::WHERE($where, $sql);
        return $sql;
    }
}
// BUILD BETWEEN



?>
