<?php
/*
 Author: Andrews54757
 License: MIT (https://github.com/ThreeLetters/SuperSQL/blob/master/LICENSE)
 Source: https://github.com/ThreeLetters/SQL-Library
 Build: v2.5.0
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
        $q = $this->db->prepare($query);
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

// index.php
class SuperSQL
{
    public $con;
    function __construct($dsn, $user, $pass)
    {
        $this->con = new Connector($dsn, $user, $pass);
    }
    function sSELECT($table, $columns = array(), $where = array(), $append = "")
    {
        $d = SimParser::SELECT($table, $columns, $where, $append);
        return $this->con->query($d);
    }
    function sINSERT($table, $data)
    {
        $d = SimParser::INSERT($table, $data);
        return $this->con->query($d);
    }
    function sUPDATE($table, $data, $where = array())
    {
        $d = SimParser::UPDATE($table, $data, $where);
        return $this->con->query($d);
    }
    function sDELETE($table, $where = array())
    {
        $d = SimParser::DELETE($table, $where);
        return $this->con->query($d);
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