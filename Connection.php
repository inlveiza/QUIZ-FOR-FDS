<?php

$host = "localhost"; 
$username = "root";  
$password = "";      
$dbname = "my_database"; 

$con = new mysqli($host, $username, $password, $dbname);

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
?>
