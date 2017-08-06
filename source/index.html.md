---
title: SuperSQL

language_tabs:
  - php

search: true
---

# SuperSQL

```php
<?php
// MySql setup
$host = "localhost";
$db = "test";
$user = "root";
$pass = "1234";

$dsn = "mysql:host=$host;port=3306;dbname=$db;charset=utf8";
$SuperSQL = new SuperSQL($dsn,$user,$pass);
?>
```

SlickInject and Medoo on steroids - The most advanced and compact library available.

### Purpose

1. To provide a very fast and efficient way to edit sql databases
2. To provide a easy method of access

### Usage

```php
<?php
new SuperSQL($dsn,$user,$pass);
?>
```

You may either

1. Use the built file (`/dist/SuperSQL.php`)
2. Use the library (include index.php)

### Build
To build this library, do 

`node builder.js`

It will build to `/dist/SuperSQL.php`

# Documentation

> Conditionals (WHERE & JOIN)

```php
<?php
$where = array(
 "arg1" => "val1", // AND arg1 = val1
 "[>>]arg2" => "val2", // AND arg2 > val2
 "[<<]arg3" => "val3", // AND arg3 < val3
 "[>=]arg4" => "val4", // AND arg4 >= val4
 "[<=]arg5" => "val5", // AND arg5 <= val5
 "[||]" => [ // Bind ||.
     "arg6" => "val6"
 ],
 "[&&][>>]" => [ // Bind >.
     "arg7" => "val7"
 ],
 "arg8" => ["val8a","val8b"]
);
?>
```

> Multi-Querying

```php
<?php
// Way 1

array(
array( // NOTE: While the all the arrays dont have to be identical, the first one should have the most items
"arg1"=> "val1",
"arg2"=> "val2"
),
array(
"arg2"=> "val3"
}
); // -> [["val1","val2"],["val1","val3"]] - Two queries

// Way 2 (only works with the data argument in INSERT and UPDATE)

array(
"arg1" => "val1",
"arg2" => array("val2","val3")
); // -> [["val1","val2"],["val1","val3"]] - Two queries
?>
```

> Multi-Table queries

```php
<?php
$SuperSQL->SELECT(["table1","table2"],[],[]);
?>
```

> Simple queries

```php
<?php
$SuperSQL->sSELECT($table,$columns,$where);

$SuperSQL->sINSERT($table,$data);

$SuperSQL->sUPDATE($table,$data,$where);

$SuperSQL->sDELETE($table,$where);
?>
```
### Conditionals

Conditional statements are extremly customisable. WHERE and JOIN clauses are conditional statements. 

<aside class="notice">
To make duplicate keys work for binds, you can add a name to the bind. Ex: `[&&]name`
</aside>

### Multi-queries

Multiqueries can be done too. This allows for highly efficient repetative queries. Note: Only the values of WHERE, JOIN, and INSERT work with this. VALUES, not KEYS.
### Multi-Table support

If you want to query multiple tables at once, put the tables in as an array

### Simple

If you are making simple queries, you may use simple functions to boost performance. Use simple functions by attatching an `s` in front of the function. The syntax is very similar to SlickInject.
### Cache

Performance is boosted for a query if an identical query before it (with different values [EG where vals, join, insert]), is made right before. You can also clear the cache by doing: `$SuperSQL->clearCache()`

## SELECT
```php
<?php
$SuperSQL->SELECT("horizon", [], [], array(
    // [>>] - Right join
    // [<<] - Left join
    // [><] - Inner join (Default)
    // [<>] - Full join
    "[><]meteors" => array("horizon.object" => "meteors.object"), // JOIN
),5); // only 5 rows
?>
```

**SuperSQL->SELECT($table, $columns, $where[,$join[, $limit);**

* `(String)table` - Table name to query
* `(Array)columns` - Array of columns to return. `[]` will query using the `*` selector. Also note, that you may use the `DISTINCT` keyword by putting it first in the array.
* `(Array)where` - Array of conditions for WHERE (See above for documentation on WHERE)
* `(Array)join` - Array of conditions for JOIN. Usage below
* `{Int}limit` - Number of rows to retrieve. Usage below.

<aside class="notice">
Note, you may also do `SuperSQL->SELECT($table, $columns, $where, $limit)`
</aside>

## INSERT
```php
<?php
$SuperSQL->INSERT("table",array(
"hello" => "world",
"SuperSQL" => "rocks"
));
?>
```

**SuperSQL->INSERT($table, $data);**

* `(String)table` - Table to insert to
* `(Array)data` - Data to insert

## UPDATE
```php
<?php
$SuperSQL->UPDATE("citizens",array(
"SuperSQL" => "To the rescue!"
),array(
"needs_help" => 1
));
?>
```

**SuperSQL->UPDATE($table, $data, $where);**

* `(String)table` - Table to insert to
* `(Array)data` - Data to update
* `(Array)where` - Conditional statements

## DELETE

```php
<?php
$SuperSQL->DELETE("persons",
"is_villain" => 1
));
?>
```

**SuperSQL->DELETE($table, $where);**

* `(String)table` - Table to insert to
* `(Array)where` - Conditional statements

