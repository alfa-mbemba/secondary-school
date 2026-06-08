<?php include 'config.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Access Denied</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .denied-container {
            text-align: center;
            padding: 50px;
            max-width: 500px;
            margin: 100px auto;
            background: #f8d7da;
            border-radius: 20px;
            border-left: 5px solid #dc3545;
        }
        .denied-container h1 {
            color: #dc3545;
            font-size: 48px;
        }
    </style>
</head>
<body>
<div class="denied-container">
    <h1>⛔ Access Denied</h1>
    <p>You do not have permission to access this page.</p>
    <p>Only <strong>Teachers</strong> can add or edit marks and results.</p>
    <a href="index.php" class="btn">⬅ Back to Dashboard</a>
    <a href="teacher_login.php" class="btn" style="background:#28a745;">👨‍🏫 Teacher Login</a>
</div>
</body>
</html>