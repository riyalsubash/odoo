<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


$servername = "localhost";
$username = "root"; 
$password = "";    
$dbname = "test_odoo"; 


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>