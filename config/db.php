<?php
// Database connection

$host = "localhost";
$user = "root"; 
$pass = "";    
$dbname = "student_ai_app";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>