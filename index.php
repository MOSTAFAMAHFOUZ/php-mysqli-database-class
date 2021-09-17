<?php 

require_once 'vendor/autoload.php';
$serverName = "localhost";
$username = "root";
$password = "";
$dbName = "your_database_name";
$db = new Src\DB_Mysqli($serverName,$username,$password,$dbName);