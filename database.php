<?php

$databaseHost = "db";
$databaseName = "sklep";
$databaseUsername = "root";
$databasePassword = "";

$db = new PDO("mysql:host={$databaseHost};dbname={$databaseName}", $databaseUsername, $databasePassword);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

return $db;