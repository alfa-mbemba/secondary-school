<?php 
include 'config.php'; 
if(!isset($_SESSION['teacher'])) header('Location: teacher_login.php');
$teacher = $_SESSION['teacher_data'];
$result_id = $_GET['id'] ?? 0;

$result = $conn->query("SELECT * FROM results WHERE id=$result_id")->fetch_assoc();
if(!$result) die("Result not found");

if(isset($_POST['update'])){
    $marks = $_POST['marks'];
    $total_marks = $_POST['total_marks'];
    $percentage = ($marks / $total_marks) * 100;
    $grade = $percentage >= 80 ? 'A' : ($percentage >= 70 ? 'B' : ($percentage >= 60 ? 'C' : ($percentage >= 50 ? 'D' : 'E')));
    
    $conn->query("UPDATE results SET marks='$marks', total_marks='$total_marks', grade='$grade' WHERE id=$result_id");
    echo "<script>alert('✅ Result updated successfully!'); window.location.href='teacher_view_results.php';</script>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Result</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>✏️ Edit Result</h2>
    
    <form method="POST">
        <label>Subject: <?= $result['subject'] ?></label>
        <label>Exam: <?= $result['exam_name'] ?></label>
        <input type="number" name="marks" value="<?= $result['marks'] ?>" required>
        <input type="number" name="total_marks" value="<?= $result['total_marks'] ?>" required>
        <button type="submit" name="update">💾 Update Result</button>
    </form>
    
    <a href="teacher_view_results.php">⬅ Back</a>
</div>
</body>
</html>