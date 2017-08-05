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
Source: https://github.com/ThreeLetters/SuperSQL
*/

include "lib/parser/Advanced.php";
include "lib/parser/Simple.php";
include "lib/connector/index.php";

use SuperSQL\AdvancedParser as Parser;
use SuperSQL\SimpleParser as Simple;
use SuperSQL\Connector as Connector;


// BUILD BETWEEN
class SuperSQL
{
    public $connector;
    
    /**
     * Creates a connection
     * @param {String} dsn - DSN of the connection
     * @param {String} user - Username
     * @param {String} pass - Password
     */
    function __construct($dsn, $user, $pass)
    {
        $this->connector = new Connector($dsn, $user, $pass);
    }
    
    /**
     * Queries a SQL table (SELECT)
     *
     * @param {String} table - SQL Table
     * @param {Array} columns - Columns to return
     * @param {Object|Array} where - Where clause
     * @param {Object|null} join - Join clause 
     * @param {String} limit - Limit clause 
     *
     * @returns {SQLResponse|SQLResponse[]}
     */
    function SELECT($table, $columns, $where, $join = null, $limit = false)
    {
        if (gettype($join) == "integer") {
            $limit = $join;
            $join  = null;
        }
        $d = Parser::SELECT($table, $columns, $where, $join, $limit);
        return $this->connector->_query($d[0], $d[1]);
    }
    
    /**
     * Inserts data into a SQL table
     *
     * @param {String} table - SQL Table
     * @param {Object|Array} data - Data to insert
     *
     * @returns {SQLResponse|SQLResponse[]}
     */
    function INSERT($table, $data)
    {
        $d = Parser::INSERT($table, $data);
        return $this->connector->_query($d[0], $d[1]);
    }
    
    /**
     * Updates a SQL table
     *
     * @param {String} table - SQL Table
     * @param {Object|Array} data - Data to update
     * @param {Object|Array} where - Where clause
     *
     * @returns {SQLResponse|SQLResponse[]}
     */
    function UPDATE($table, $data, $where)
    {
        $d = Parser::UPDATE($table, $data, $where);
        return $this->connector->_query($d[0], $d[1]);
    }
    
    /**
     * Deletes from a SQL table
     *
     * @param {String} table - SQL Table
     * @param {Object|Array} where - Where clause
     *
     * @returns {SQLResponse|SQLResponse[]}
     */
    function DELETE($table, $where)
    {
        $d = Parser::DELETE($table, $where);
        return $this->connector->_query($d[0], $d[1]);
    }
    
    /**
     * Queries a SQL table (SELECT) (Simple)
     *
     * @param {String} table - SQL Table
     * @param {Array} columns - Columns to return
     * @param {Object} where - Where clause
     * @param {String} append - SQL append
     *
     * @returns {SQLResponse}
     */
    function sSELECT($table, $columns, $where, $append = "")
    {
        $d = Simple::SELECT($table, $columns, $where, $append);
        return $this->connector->_query($d[0], $d[1]);
    }
    
    /**
     * Inserts data into a SQL table (Simple)
     *
     * @param {String} table - SQL Table
     * @param {Object} data - Data to insert
     *
     * @returns {SQLResponse}
     */
    function sINSERT($table, $data)
    {
        $d = Simple::INSERT($table, $data);
        return $this->connector->_query($d[0], $d[1]);
    }
    
    /**
     * Updates a SQL table (Simple)
     *
     * @param {String} table - SQL Table
     * @param {Object} data - Data to update
     * @param {Object} where - Where clause
     *
     * @returns {SQLResponse}
     */
    function sUPDATE($table, $data, $where)
    {
        $d = Simple::UPDATE($table, $data, $where);
        return $this->connector->_query($d[0], $d[1]);
    }
    
    /**
     * Deletes from a SQL table (Simple)
     *
     * @param {String} table - SQL Table
     * @param {Object} where - Where clause
     *
     * @returns {SQLResponse}
     */
    function sDELETE($table, $where)
    {
        $d = Simple::DELETE($table, $where);
        return $this->connector->_query($d[0], $d[1]);
    }
    
    /**
     * Query
     */
    function query($query)
    {
        return $this->connector->query($query);
    }
    /**
     * Closes the connection
     */
    function close()
    {
        $this->connector->close();
    }
    
    /**
     * Turns on dev mode
     */
    function dev()
    {
        $this->connector->dev = true;
    }
    
    /**
     * Get log
     */
    function getLog()
    {
        return $this->connector->log;
    }
    
    
}
// BUILD BETWEEN

?>