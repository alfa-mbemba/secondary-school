<?php
echo "<h1>Test Page - Railway is Working!</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server Time: " . date('Y-m-d H:i:s') . "</p>";

// Test database connection
$host = getenv('DB_HOST') ?: 'sql.freesqldatabase.com';
$user = getenv('DB_USER') ?: '';
$pass = getenv('DB_PASS') ?: '';
$db = getenv('DB_NAME') ?: '';

echo "<h3>Database Test:</h3>";
echo "<p>Host: $host</p>";
echo "<p>User: $user</p>";
echo "<p>Database: $db</p>";

if($host && $user && $pass && $db) {
    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        echo "<p style='color:red'>❌ Connection Failed: " . $conn->connect_error . "</p>";
    } else {
        echo "<p style='color:green'>✅ Database Connected!</p>";
        $conn->close();
    }
} else {
    echo "<p style='color:orange'>⚠️ Database credentials not set. Add them in Environment Variables.</p>";
}
?>