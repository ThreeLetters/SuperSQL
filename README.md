# SQL-Library
SlickInject and Medoo on steroids - The most advanced and compact library available.

## Purpose

1. To provide a very fast and efficient way to edit sql databases
2. To provide a easy method of access

## Documentation
```php
// MySql setup
$host = "localhost";
$db = "test";
$user = "root";
$pass = "1234";

$dsn = "mysql:host=$host;port=3306;dbname=$db;charset=utf8";
$s = new SQLib($dsn,$user,$pass);
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
 "[||]arg6" => "val6", // OR arg6 = val 6
 "[!!]arg7" => "val7", // NOT arg7 = val 7
 "[||][>>]arg8" => "val8" // OR arg8 > val8
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

// Way 2

array(
"arg1" => "val1",
"arg2" => array("val2","val3")
); // -> [["val1","val2"],["val1","val3"]] - Two queries
```

#### Cache
Performance is boosted for a query if an identical query before it (with different values [EG where vals, join, insert]), is made right before

### SELECT
> **SQLib->SELECT($table, $columns, $where[,$join);**

#### $table
The name of the table, as a string.

### INSERT
> **SQLib->INSERT($table, $data);**

### UPDATE
> **SQLib->UPDATE($table, $data, $where);**

### DELETE
> **SQLib->DELETE($table, $where);**
