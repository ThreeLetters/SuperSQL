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
```php
// MySql setup
$host = "localhost";
$db = "test";
$user = "root";
$pass = "1234";

$dsn = "mysql:host=$host;port=3306;dbname=$db;charset=utf8";
$SuperSQL = new SuperSQL($dsn,$user,$pass);
```
## Build
To build this library, do 

> node builder.js

It will build to /dist/SuperSQL.php

## Documentation

Full documentation is here: https://threeletters.github.io/SuperSQL/

![screen shot 2017-08-06 at 10 12 04 pm](https://user-images.githubusercontent.com/13282284/29009780-4fb22120-7af4-11e7-8621-a65ce32f69c2.png)


