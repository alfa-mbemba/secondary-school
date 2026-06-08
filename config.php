<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'school_management(1)';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
session_start();
?>