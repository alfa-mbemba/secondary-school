<?php
// InfinityFree Database Creator Script
// ================================================

// Credentials yako ya InfinityFree
$host = 'sql123.infinityfree.net';
$user = 'if0_42132591';
$password = 'KDAjCggORndnfA';  // Password yako ya infinityfree
$database = 'if0_42132591_school_db';

// ================================================
echo "<h1>Setting Up Database...</h1>";

// 1. Unganisha kwenye MySQL server (bila database)
$conn = new mysqli($host, $user, $password);

if ($conn->connect_error) {
    die("<p style='color:red'>❌ Connection failed: " . $conn->connect_error . "</p>");
}
echo "<p style='color:green'>✅ Connected to MySQL server</p>";

// 2. Unda database
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if ($conn->query($sql) === TRUE) {
    echo "<p style='color:green'>✅ Database '$database' created or already exists</p>";
} else {
    echo "<p style='color:red'>❌ Error creating database: " . $conn->error . "</p>";
}

// 3. Chagua database
$conn->select_db($database);
echo "<p style='color:green'>✅ Selected database: $database</p>";

// 4. Unda tables
$tables_sql = "
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admission_no VARCHAR(20) UNIQUE,
    full_name VARCHAR(100),
    class VARCHAR(20),
    parent_phone VARCHAR(15),
    address TEXT,
    fee_paid DECIMAL(10,2) DEFAULT 0,
    total_fees DECIMAL(10,2) DEFAULT 5000,
    registered_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id VARCHAR(20) UNIQUE,
    full_name VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(15),
    subject VARCHAR(50),
    class_assigned VARCHAR(20),
    password VARCHAR(255),
    last_password_change DATETIME,
    registered_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS parents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    full_name VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(15),
    student_admission_no VARCHAR(20),
    password VARCHAR(255),
    registered_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    exam_name VARCHAR(50),
    subject VARCHAR(50),
    marks INT,
    total_marks INT DEFAULT 100,
    grade CHAR(2)
);

CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    teacher_id INT,
    date DATE,
    status ENUM('Present', 'Absent', 'Late'),
    remark TEXT
);

CREATE TABLE IF NOT EXISTS exam_timetable (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_name VARCHAR(50),
    class VARCHAR(20),
    subject VARCHAR(50),
    exam_date DATE,
    start_time TIME,
    end_time TIME,
    venue VARCHAR(100),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS fee_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    amount DECIMAL(10,2),
    payment_date DATE,
    payment_method VARCHAR(50),
    transaction_id VARCHAR(100),
    receipt_no VARCHAR(50),
    status ENUM('Pending', 'Completed', 'Failed') DEFAULT 'Completed'
);
";

// Execute multi query
if ($conn->multi_query($tables_sql)) {
    do {
        // Consume results
        while ($conn->more_results() && $conn->next_result()) {
            // Skip results
        }
    } while (false);
    echo "<p style='color:green'>✅ Tables created successfully!</p>";
} else {
    echo "<p style='color:red'>❌ Error creating tables: " . $conn->error . "</p>";
}

// 5. Insert default data
$insert_sql = "
INSERT IGNORE INTO admin (username, password) VALUES ('admin', MD5('admin123'));

INSERT IGNORE INTO teachers (teacher_id, full_name, email, phone, subject, class_assigned, password) 
VALUES ('TCH001', 'John Doe', 'john@school.com', '0712345678', 'Mathematics', 'Form 1', MD5('teacher123'));

INSERT IGNORE INTO students (admission_no, full_name, class, parent_phone) 
VALUES ('STU001', 'John Student', 'Form 1', '0712345678');

INSERT IGNORE INTO parents (username, full_name, email, phone, student_admission_no, password) 
VALUES ('STU001', 'Parent of John Student', 'parent@email.com', '0712345678', 'STU001', MD5('parent123'));
";

if ($conn->multi_query($insert_sql)) {
    echo "<p style='color:green'>✅ Default data inserted successfully!</p>";
} else {
    echo "<p style='color:orange'>⚠️ Note: " . $conn->error . "</p>";
}

$conn->close();

echo "<hr>";
echo "<h2>✅ Database setup complete!</h2>";
echo "<p>You can now <a href='index.php'>go to your website</a> and login with:</p>";
echo "<ul>
    <li><strong>Admin:</strong> admin / admin123</li>
    <li><strong>Teacher:</strong> TCH001 / teacher123</li>
    <li><strong>Parent:</strong> STU001 (no password)</li>
</ul>";
echo "<p style='color:red; font-weight:bold;'>⚠️ IMPORTANT: Delete this file (create_db.php) after successful setup for security!</p>";
?>