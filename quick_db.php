<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'sql123.infinityfree.net';
$user = 'if0_42132591';
$pass = 'KDAjCggORndnfA';
$db = 'if0_42132591_school_db';

echo "Connecting...<br>";
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected!<br>";

// Create database
$conn->query("CREATE DATABASE IF NOT EXISTS $db");
$conn->select_db($db);
echo "Database ready!<br>";

// Simple tables
$conn->query("CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255)
)");

$conn->query("INSERT IGNORE INTO admin (username, password) VALUES ('admin', MD5('admin123'))");

$conn->query("CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admission_no VARCHAR(20) UNIQUE,
    full_name VARCHAR(100),
    class VARCHAR(20),
    parent_phone VARCHAR(15)
)");

$conn->query("INSERT IGNORE INTO students (admission_no, full_name, class, parent_phone) 
    VALUES ('STU001', 'John Student', 'Form 1', '0712345678')");

$conn->query("CREATE TABLE IF NOT EXISTS teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id VARCHAR(20) UNIQUE,
    full_name VARCHAR(100),
    password VARCHAR(255)
)");

$conn->query("INSERT IGNORE INTO teachers (teacher_id, full_name, password) 
    VALUES ('TCH001', 'John Doe', MD5('teacher123'))");

echo "<h2 style='color:green'>✅ Database setup complete!</h2>";
echo "<a href='index.php'>Go to Website</a>";
$conn->close();
?>