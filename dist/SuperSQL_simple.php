<?php
/*
 Author: Andrews54757
 License: MIT (https://github.com/ThreeLetters/SuperSQL/blob/master/LICENSE)
 Source: https://github.com/ThreeLetters/SQL-Library
 Build: v1.0.1
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