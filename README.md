# SuperSQL
SlickInject and Medoo on steroids - The most advanced and compact library available.

## Purpose

1. To provide a very fast and efficient way to edit sql databases
2. To provide a easy method of access

## Usage
You may either

1. Use the built file (/dist/SuperSQL.php)
2. Use the library (include index.php)

```php
new SuperSQL($dsn,$user,$pass);
```
## Build
To build this library, do 

> node builder.js

It will build to /dist/SuperSQL.php

## Documentation

Full documentation is here: https://threeletters.github.io/SuperSQL/


```php
// MySql setup
$host = "localhost";
$db = "test";
$user = "root";
$pass = "1234";

$dsn = "mysql:host=$host;port=3306;dbname=$db;charset=utf8";
$SuperSQL = new SuperSQL($dsn,$user,$pass);
```

### Notes
#### Conditionals
Conditional statements are extremly customisable. WHERE and JOIN clauses are conditional statements. 

```php
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
```

#### Multi-queries
Multiqueries can be done too. This allows for highly efficient repetative queries. Note: Only the values of WHERE, JOIN, and INSERT work with this. VALUES, not KEYS.

```php
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
```
#### Multi-Table support
If you want to query multiple tables at once, put the tables in as an array

```php
$SuperSQL->SELECT(["table1","table2"],[],[]);
```

#### Type Casting
If you want to set the type of the input, you can set it by adding `[type] (replace type with type)`.

```php
$SuperSQL->INSERT("sensitive_data",[ // NOTE: Also works with any other query. ALSO NOTE: Types are case-insensitive
   "nuclear_codes[int]" => 138148347734, // Integer (Use [int] or [integer]
   "in_state_of_emergency[bool]" => false, // Boolean (Use [bool] or [boolean]
   "secret_files[lob]" => $file // Large Objects/Resources (Use [lob] or [resource])
   "fake_data[null]" => null // Null values (use [null])
]);
```

#### Simple
If you are making simple queries, you may use simple functions to boost performance. Use simple functions by attatching an `s` in front of the function. The syntax is very similar to SlickInject.

```php
$SuperSQL->sSELECT($table,$columns,$where);

$SuperSQL->sINSERT($table,$data);

$SuperSQL->sUPDATE($table,$data,$where);

$SuperSQL->sDELETE($table,$where);
```

#### Cache
Performance is boosted for a query if an identical query before it (with different values [EG where vals, join, insert]), is made right before. You can also clear the cache by doing: `$SuperSQL->clearCache()`

### SELECT
> **SuperSQL->SELECT($table, $columns, $where[,$join[, $limit);**

* `(String)table` - Table name to query
* `(Array)columns` - Array of columns to return. `[]` will query using the `*` selector. Also note, that you may use the `DISTINCT` keyword by putting it first in the array.
* `(Array)where` - Array of conditions for WHERE (See above for documentation on WHERE)
* `(Array)join` - Array of conditions for JOIN. Usage below
* `{Int}limit` - Number of rows to retrieve. Usage below. Note, you may also do `SuperSQL->SELECT($table, $columns, $where, $limit)`

```php
$SuperSQL->SELECT("horizon", [], [], array(
    // [>>] - Right join
    // [<<] - Left join
    // [><] - Inner join (Default)
    // [<>] - Full join
    "[><]meteors" => array("horizon.object" => "meteors.object"), // JOIN
),5); // only 5 rows
```

### INSERT
> **SuperSQL->INSERT($table, $data);**

* `(String)table` - Table to insert to
* `(Array)data` - Data to insert

```php
$SuperSQL->INSERT("table",array(
"hello" => "world",
"SuperSQL" => "rocks"
));
```
### UPDATE
> **SuperSQL->UPDATE($table, $data, $where);**

* `(String)table` - Table to insert to
* `(Array)data` - Data to update
* `(Array)where` - Conditional statements

```php
$SuperSQL->UPDATE("citizens",array(
"SuperSQL" => "To the rescue!"
),array(
"needs_help" => 1
));
```

### DELETE
> **SuperSQL->DELETE($table, $where);**

* `(String)table` - Table to insert to
* `(Array)where` - Conditional statements

```php
$SuperSQL->DELETE("persons",
"is_villain" => 1
));
```

