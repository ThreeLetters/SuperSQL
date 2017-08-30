<?php

include "../autoload.php";

/*
include "../dist/SuperSQL.php";
include "../dist/SuperSQL_helper.php";
*/
$Helper = new SuperSQL\SQLHelper("localhost","mydb","root","1234");

$Helper->SELECT();

$Helper->INSERT();

$Helper->UPDATE();

$Helper->DELETE();

$Helper->GET();

$Helper->REPLACE();

$Helper->change();
$Helper->getCon();






?>
