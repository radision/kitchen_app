<?php

$db_host = 'localhost';
$db_user = 'root';
$db_pass = 'red.12358';
$db_name = 'kitchen';

$link = mysql_connect($db_host, $db_user, $db_pass);
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
mysql_select_db($db_name);
var_dump($link);
