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
class Response
{
    public $result;
    public $affected;
    public $ind;
    public $error;
    public $errorData;
    
    /**
     * Gets data from a query
     */
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
    
    /**
     * Returns error data if there is one
     */
    function error()
    {
        return $this->error ? $this->errorData : false;
    }
    
    /**
     * Gets data from the query
     *
     * @returns {Array}
     */
    function getData()
    {
        return $this->result;
    }
    
    /**
     * Gets number of affected rows from the query
     *
     * @returns {Int}
     */
    function getAffected()
    {
        return $this->affected;
    }
    
    /**
     * Gets next row
     */
    function next()
    {
        return $this->result[$this->ind++];
    }
    
    /**
     * Resets ititerator
     */
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
    
    /**
     * Creates a connection
     * @param {String} dsn - DSN of the connection
     * @param {String} user - Username
     * @param {String} pass - Password
     */
    function __construct($dsn, $user, $pass)
    {
        $this->db  = new \PDO($dsn, $user, $pass);
        $this->log = array();
    }
    
    /**
     * Queries database
     * @param {String} query - Query to make
     *
     * @returns {SQLResponse}
     */
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
    
    /**
     * Queries database Advanced
     * @param {String} sql - Base query
     * @param {Array} values - array of values and types
     * @param {Array} insert - Multi-query inserts
     * @param {String} typeString - Type checking
     *
     * @returns {SQLResponse|SQLResponse[]}
     */
    function _query($sql, $values, $insert, $typeString, $outtypes = array())
    {
        // echo json_encode(array($sql,$insert));
        // return;
        if (isset($this->queries[$sql . "|" . $typeString])) { // Cache
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
        
        if (count($insert) == 0) { // Single query
            $e = $q->execute();
            return new Response($q, $e, $outtypes);
        } else { // Multi Query
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
    
    /**
     * Closes the connection
     */
    function close()
    {
        $this->db      = null;
        $this->queries = null;
        
    }
    /**
     * Clears cache
     */
    function clearCache()
    {
        $this->queries = array();
    }
}
// BUILD BETWEEN
?>
