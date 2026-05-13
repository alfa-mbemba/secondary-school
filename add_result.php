<?php 
include 'config.php'; 

// Redirect teachers to teacher_add_marks.php
if(isset($_SESSION['teacher'])){
    header('Location: teacher_add_marks.php');
    exit();
}

// If admin tries to access, show error
if(isset($_SESSION['admin'])){
    header('Location: access_denied.php');
    exit();
}

// Otherwise redirect to login
header('Location: index.php');
?>