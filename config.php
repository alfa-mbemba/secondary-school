<?php
// Database credentials - Kwa Railway
$host = getenv('DB_HOST') ?: 'sql.freesqldatabase.com';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
$database = getenv('DB_NAME') ?: 'school_management';
$port = getenv('DB_PORT') ?: 3306;

// Unda connection
$conn = new mysqli($host, $user, $password, $database, $port);

// Angalia connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Weka charset
$conn->set_charset("utf8mb4");

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Base URL
define('BASE_URL', getenv('RAILWAY_PUBLIC_DOMAIN') ? 'https://' . getenv('RAILWAY_PUBLIC_DOMAIN') : 'http://localhost');
?>