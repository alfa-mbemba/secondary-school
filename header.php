<?php
// header.php - Include this in all pages for consistent navigation
if(!isset($_SESSION)) session_start();

function getUserRole(){
    if(isset($_SESSION['admin'])) return 'admin';
    if(isset($_SESSION['teacher'])) return 'teacher';
    if(isset($_SESSION['parent_logged_in'])) return 'parent';
    return 'guest';
}

$role = getUserRole();
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        .top-nav {
            background: #333;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .top-nav a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
        }
    </style>
</head>
<body>
<div class="top-nav">
    <div>🏫 School Management System</div>
    <div>
        <?php if($role == 'admin'): ?>
            <span>👑 Admin: <?= $_SESSION['admin'] ?></span>
            <a href="index.php">Dashboard</a>
        <?php elseif($role == 'teacher'): ?>
            <span>👨‍🏫 Teacher: <?= $_SESSION['teacher_data']['full_name'] ?></span>
            <a href="teacher_dashboard.php">Dashboard</a>
            <a href="teacher_add_marks.php">Add Marks</a>
        <?php elseif($role == 'parent'): ?>
            <span>👪 Parent: <?= $_SESSION['parent_name'] ?></span>
            <a href="parent_dashboard.php">Dashboard</a>
        <?php endif; ?>
        <a href="logout.php">Logout</a>
    </div>
</div>
</body>
</html>