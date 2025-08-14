<?php

$servername = "sql305.infinityfree.com";  
$username = "if0_39709331";      
$password = "lvBysYvm7n2cog";   
$dbname = "if0_39709331_elegantimage"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
if (!$conn->set_charset("utf8mb4")) {
}

?>