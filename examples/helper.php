<?php

include "../index.php";

/*
include "../dist/SuperSQL.php";
include "../dist/SuperSQL_helper.php";
*/
$Helper = new $SQLHelper("localhost","mydb","root","1234");

$Helper->SELECT();

$Helper->INSERT();

$Helper->UPDATE();

$Helper->DELETE();

$Helper->GET();

$Helper->REPLACE();

$Helper->change();
$Helper->getCon();






?>
