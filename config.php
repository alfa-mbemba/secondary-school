<?php
// Database credentials kutoka FreeSQLDatabase
$host = 'sql10.freesqldatabase.com';     // Host yako
$user = 'sql10829785';                  // Badilisha na username yako
$password = 'nx9DBMhZyG';            // Badilisha na password yako
$database = 'sql10829785';              // Badilisha na jina la database yako
$port = 3306;

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

// Base URL kwa Render
define('BASE_URL', getenv('RENDER_EXTERNAL_URL') ?: 'http://localhost');
?>