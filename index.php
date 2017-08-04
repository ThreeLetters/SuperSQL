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

include "lib/parser/index.php";
include "lib/connector/index.php";

use SuperSQL\Parser as Parser;
use SuperSQL\Connector as Connector;

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
    * 
    * @returns {SQLResponse|SQLResponse[]}
    */
    function SELECT($table, $columns, $where, $join = null)
    {
        $d = Parser::SELECT($table, $columns, $where, $join);
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
    
    
}


?>
