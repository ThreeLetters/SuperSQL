<?php
/*
 Author: Andrews54757
 License: MIT (https://github.com/ThreeLetters/SuperSQL/blob/master/LICENSE)
 Source: https://github.com/ThreeLetters/SQL-Library
 Build: v1.0.2
 Built on: 14/08/2017
*/

// lib/connector/index.php
class Response
{
    public $result;
    public $affected;
    public $ind = 0;
    public $error;
    public $errorData;
    public $outTypes;
    public $complete = false;
    public $stmt;
    function __construct($data, $error, &$outtypes, &$mode)
    {
        $this->error = !$error;
        if (!$error) {
            $this->errorData = $data->errorInfo();
        } else {
            $this->outTypes = $outtypes;
            $this->init($data,$mode);
            $this->affected = $data->rowCount();
        }
    }
    private function init(&$data, &$mode) {
        if ($mode === 0) { 
            $outtypes = $this->outTypes;
            $d = $data->fetchAll();
            if ($outtypes) {
                foreach ($d as $i => &$row) {
                    $this->map($row,$outtypes);
                }
            }
            $this->result = $d;
            $this->complete = true;
        } else if ($mode === 1) { 
            $this->stmt = $data;
            $this->result = array();
        }
    }
    function close() {
            $this->complete = true;
        if ($this->stmt) {
            $this->stmt->closeCursor();
            $this->stmt = null;
        }
    }
    private function fetchNextRow() {
       $row = $this->stmt->fetch();
        if ($row) {
         if ($this->outTypes) {
        $this->map($row,$this->outTypes);   
        }
        array_push($this->result,$row);
        return $row;
        } else {
            $this->complete = true;
            $this->stmt->closeCursor();
            $this->stmt = null;
            return false;
        }
    }
    private function fetchAll() {
        while ($row = $this->fetchNextRow()) {
        }
    }
    function map(&$row,&$outtypes) {
                    foreach ($outtypes as $col => $dt) {
                        if (isset($row[$col])) {
                            switch ($dt) {
                                case 'int':
                                    $row[$col] = (int)$row[$col];
                                    break;
                                case 'string':
                                     $row[$col] = (string)$row[$col];
                                    break;
                                case 'bool':
                                     $row[$col] = $row[$col] ? true : false;
                                    break;
                                case 'json':
                                    $row[$col] = json_decode($row[$col]);
                                    break;
                                case 'obj':
                                    $row[$col] = unserialize($row[$col]);
                                    break;
                            }
                        }
                    }   
    }
    function error()
    {
        return $this->error ? $this->errorData : false;
    }
    function getData($current = false)
    {
        if (!$this->complete && !$current) $this->fetchAll();
        return $this->result;
    }
    function getAffected()
    {
        return $this->affected;
    }
    function countRows() {
        return count($this->result);
    }
    function next()
    {
        if (isset($this->result[$this->ind])) {
            return $this->result[$this->ind++];
        } else if (!$this->complete) {
            $row = $this->fetchNextRow();
            $this->ind++;
            return $row;
        } else {
            return false;
        }
    }
    function reset()
    {
        $this->ind = 0;
    }
}
class Connector
{
    public $db;
    public $log = array();
    public $dev = false;
    function __construct($dsn, $user, $pass)
    {
        $this->db  = new \PDO($dsn, $user, $pass);
        $this->log = array();
    }
    function query($query,$obj = null,$outtypes = null, $mode = 0)
    {
            $q = $this->db->prepare($query);
        if ($obj) $e = $q->execute($obj);
        else $e = $q->execute();
        if ($this->dev)
            array_push($this->log, array(
                $query,
                $obj
            ));
        if ($mode !== 3) {
         return new Response($q,$e,$outtypes,$mode);   
        } else {
        return $q;
        }
    }
    function _query(&$sql, $values, &$insert, &$outtypes = null, $mode = 0)
    {
        $q                   = $this->db->prepare($sql);
         if ($this->dev) 
             array_push($this->log,array(
                    $sql,
                    $values,
                    $insert
             ));
        foreach ($values as $key => &$va) {
                $q->bindParam($key + 1, $va[0],$va[1]);
        }
         $e = $q->execute();
        if (!isset($insert[0])) { 
            return new Response($q, $e, $outtypes, $mode);
        } else { 
            $responses = array();
            array_push($responses,new Response($q, $e, $outtypes, 0));
            foreach ($insert as $key => $value) {
                foreach ($value as $k => &$val) {
                    $values[$k][0] = $val;
                }
                $e = $q->execute();
                array_push($responses, new Response($q, $e, $outtypes, 0));
            }
            return $responses;
        }
    }
    function close()
    {
        $this->db      = null;
        $this->queries = null;
    }
}

// lib/parser/Simple.php
class SimParser
{
    public static function WHERE($where, &$sql, &$insert)
    {
        if (!empty($where)) {
            $sql .= ' WHERE ';
            $i = 0;
            foreach ($where as $key => $value) {
                if ($i !== 0) {
                    $sql .= ' AND ';
                }
                $sql .= '`' . $key . '` = ?';
                array_push($insert,$value);
                $i++;
            }
        }
    }
    public static function SELECT($table, $columns, $where, $append)
    {
        $sql    = 'SELECT ';
        $insert = array();
        if (!isset($columns[0])) { 
            $sql .= '*';
        } else { 
            $len    = count($columns);
            for ($i = 0; $i < $len; $i++) {
                if ($i !== 0) {
                    $sql .= ', ';
                }
                $sql .= '`' . $columns[$i] . '`';
            }
        }
        $sql .= ' FROM `' . $table . '`';
        self::WHERE($where, $sql, $insert);
       if ($append) $sql .= ' ' . $append;
        return array(
            $sql,
            $insert
        );
    }
    public static function INSERT($table, $data)
    {
        $sql    = 'INSERT INTO `' . $table . '` (';
        $add    = ') VALUES (';
        $insert = array();
        $i = 0;
        foreach ($data as $key => $value) {
            if ($i !== 0) {
                $sql .= ', ';
                $add .= ', ';
            }
            $sql .= '`' . $key . '`';
            $add .= '?';
            array_push($insert, $value);
            $i++;
        }
        $sql .= $add . ')';
        return array(
            $sql,
            $insert
        );
    }
    public static function UPDATE($table, $data, $where)
    {
        $sql    = 'UPDATE `' . $table . '` SET ';
        $insert = array();
        $i = 0;
        foreach ($data as $key => $value) {
            if ($i !== 0) {
                $sql .= ', ';
            }
            $sql .= '`' . $key . '` = ?';
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
        $sql    = 'DELETE FROM `' . $table . '`';
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
    public $lockMode = false;
    function __construct($dsn, $user, $pass)
    {
        $this->con = new Connector($dsn, $user, $pass);
    }
    function sSELECT($table, $columns = array(), $where = array(), $append = "")
    {
        $d = SimParser::SELECT($table, $columns, $where, $append);
        return $this->con->query($d[0], $d[1],null,$this->lockMode ? 0 : 1);
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
    function query($query, $obj = null,$outtypes = null, $mode = 0)
    {
        return $this->con->query($query, $obj, $outtypes, $mode);
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
    function transact($func) {
        $this->con->db->beginTransaction();
        $r = $func($this);
        if ($r === false)
            $this->con->db->rollBack();
         else 
            $this->con->db->commit();
        return $r;
    }
    function modeLock($val) {
        $this->lockMode = $val;
    }
}
?>