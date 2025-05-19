<?php

$servername = "localhost";  
$username = "root";      
$password = "sachi2003";   
$dbname = "elegant_image"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
if (!$conn->set_charset("utf8mb4")) {
}

?>