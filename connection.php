<?php
// db_connect.php

$servername = "localhost";  
$username = "root";      
$password = "sachi2003";   
$dbname = "elegant_image"; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // Production වලදී මේ error එක user ට පෙන්නන්න එපා. Log කරන්න.
    die("Database Connection Failed: " . $conn->connect_error);
}

// Set character set to UTF-8 for Sinhala or other special characters
if (!$conn->set_charset("utf8mb4")) {
    // printf("Error loading character set utf8mb4: %s\n", $conn->error); // For debugging
}

// $conn variable එක දැන් අනිත් PHP files වලට database එකට connect වෙන්න පාවිච්චි කරන්න පුළුවන්.
?>