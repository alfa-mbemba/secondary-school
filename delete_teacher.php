<?php
include 'config.php';
if(!isset($_SESSION['admin'])) header('Location: index.php');
$id = $_GET['id'];
$conn->query("DELETE FROM teachers WHERE id=$id");
header("Location: manage_teachers.php");
?>