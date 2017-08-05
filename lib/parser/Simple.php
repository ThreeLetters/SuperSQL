<?php
namespace SuperSQL;

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
 Source: https://github.com/ThreeLetters/SuperSQL
*/

// BUILD BETWEEN
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
// BUILD BETWEEN



?>