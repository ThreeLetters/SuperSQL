# SuperSQL
SlickInject and Medoo on steroids - The most advanced and compact library available.
## Purpose

1. To provide a very fast and efficient way to edit sql databases
2. To provide a easy method of access

## Notes
#### Missing features.
* `ORDER BY` not supported as you can sort things with php too - However, a pr would be welcome
* `LIMIT` - only LIMIT is supported, so some databases might not be able to handle this

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

## License

```
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
```

