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
    function query($query)
    {
        $q = $this->db->prepare($query);
        $q->execute();
        if ($this->dev)
            array_push($this->log, array(
                $query
            ));
        return new Response($q);
    }
    function _query($sql, $insert)
    {
        if (isset($this->queries[$sql])) { 
            $q = $this->queries[$sql];
            if ($this->dev)
                array_push($this->log, array(
                    "fromcache",
                    $sql,
                    $insert
                ));
        } else {
            $q                   = $this->db->prepare($sql);
            $this->queries[$sql] = $q;
            if ($this->dev)
                array_push($this->log, array(
                    $sql,
                    $insert
                ));
        }
        if (count($insert) == 1) { 
            $e = $q->execute($insert[0]);
            return new Response($q, $e);
        } else { 
            $responses = array();
            foreach ($insert as $key => $value) {
                $e = $q->execute($insert[0]);
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
class SimpleParser
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
                array_push($insert[0], $value);
                $i++;
            }
        }
    }
    public static function SELECT($table, $columns, $where, $append)
    {
        $sql    = "SELECT ";
        $insert = array(
            array()
        );
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
        $sql .= "FROM `" . $table . "`";
        self::WHERE($where, $sql, $insert);
        $sql .= " " . $append;
        return array(
            $sql,
            $insert
        );
    }
    public static function INSERT($table, $data)
    {
        $sql    = "INSERT INTO `" . $table . "` (";
        $add    = ") VALUES (";
        $insert = array(
            array()
        );
        $i = 0;
        foreach ($data as $key => $value) {
            if ($i != 0) {
                $sql .= ", ";
                $add .= ", ";
            }
            $sql .= "`" . $key . "`";
            $add .= "?";
            array_push($insert[0], $value);
            $i++;
        }
        $sql .= $add;
        return array(
            $sql,
            $insert
        );
    }
    public static function UPDATE($table, $data, $where)
    {
        $sql    = "UPDATE `" . $table . "` SET ";
        $insert = array(
            array()
        );
        $i = 0;
        foreach ($data as $key => $value) {
            if ($i != 0) {
                $sql .= ", ";
            }
            $sql .= "`" . $key . "` = ?";
            array_push($insert[0], $value);
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
        $insert = array(
            array()
        );
        self::WHERE($where, $sql, $insert);
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
    function sSELECT($table, $columns, $where, $append = "")
    {
        $d = SimpleParser::SELECT($table, $columns, $where, $append);
        return $this->connector->_query($d[0], $d[1]);
    }
    function sINSERT($table, $data)
    {
        $d = SimpleParser::INSERT($table, $data);
        return $this->connector->_query($d[0], $d[1]);
    }
    function sUPDATE($table, $data, $where)
    {
        $d = SimpleParser::UPDATE($table, $data, $where);
        return $this->connector->_query($d[0], $d[1]);
    }
    function sDELETE($table, $where)
    {
        $d = SimpleParser::DELETE($table, $where);
        return $this->connector->_query($d[0], $d[1]);
    }
    function query($query)
    {
        return $this->connector->query($query);
    }
    function close()
    {
        $this->connector->close();
    }
    function dev()
    {
        $this->connector->dev = true;
    }
    function getLog()
    {
        return $this->connector->log;
    }
    function clearCache() 
    {
        $this->connector->clearCache();
    }
}
?>