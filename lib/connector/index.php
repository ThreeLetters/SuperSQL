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
    
    /**
     * Queries database efficiently
     * @param {String} sql - Base query
     * @param {Array} insert - array of args
     *
     * @returns {SQLResponse|SQLResponse[]}
     */
    function _query($sql, $insert)
    {
        // echo json_encode(array($sql,$insert));
        // return;
        if (isset($this->queries[$sql])) { // Cache
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
        
        if (count($insert) == 1) { // Single query
            $e = $q->execute($insert[0]);
            return new Response($q, $e);
        } else { // Multi Query
            $responses = array();
            foreach ($insert as $key => $value) {
                $e = $q->execute($insert[0]);
                array_push($responses, new Response($q, $e));
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
