# SQL-Library
SlickInject and Medoo on steroids - The most advanced and compact library available.

## Purpose

1. To provide a very fast and efficient way to edit sql databases
2. To provide a easy method of access

## Documentation
### Notes
#### Conditionals
Conditional statements are extremly customisable. WHERE and JOIN clauses are conditional statements. 

```
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

#### Cache


### SELECT
> **SQLib->SELECT($table, $columns, $where[,$join);**



### INSERT
> **SQLib->INSERT($table, $data);**

### UPDATE
> **SQLib->UPDATE($table, $data, $where);**

### DELETE
> **SQLib->DELETE($table, $where);**
