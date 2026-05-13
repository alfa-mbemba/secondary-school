<?php 
include 'config.php'; 

// Only teachers can access this page
if(!isset($_SESSION['teacher'])){
    header('Location: access_denied.php');
    exit();
}

$teacher = $_SESSION['teacher_data'];
// ... rest of the page
if(!isset($_SESSION['teacher'])) header('Location: teacher_login.php');
$teacher = $_SESSION['teacher_data'];
$class = $teacher['class_assigned'];

// Handle attendance submission
if(isset($_POST['save_attendance'])){
    $date = $_POST['attendance_date'];
    foreach($_POST['status'] as $student_id => $status){
        $remark = $_POST['remark'][$student_id] ?? '';
        $conn->query("INSERT INTO attendance (student_id, teacher_id, date, status, remark) 
                       VALUES ('$student_id', '{$teacher['id']}', '$date', '$status', '$remark')");
    }
    echo "<script>alert('✅ Attendance saved for $date');</script>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mark Attendance</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>📋 Mark Attendance - <?= $class ?></h2>
    <form method="POST">
        <label>Date:</label>
        <input type="date" name="attendance_date" value="<?= date('Y-m-d') ?>" required>
        
        <table border="1" cellpadding="8">
            <tr>
                <th>Admission No</th>
                <th>Student Name</th>
                <th>Status</th>
                <th>Remark</th>
            </tr>
            <?php
            $students = $conn->query("SELECT * FROM students WHERE class='$class' ORDER BY admission_no");
            while($student = $students->fetch_assoc()):
            ?>
            <tr>
                <td><?= $student['admission_no'] ?></td>
                <td><?= $student['full_name'] ?></td>
                <td>
                    <select name="status[<?= $student['id'] ?>]" required>
                        <option value="Present">✅ Present</option>
                        <option value="Absent">❌ Absent</option>
                        <option value="Late">⏰ Late</option>
                    </select>
                </td>
                <td><input type="text" name="remark[<?= $student['id'] ?>]" placeholder="Optional"></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <button type="submit" name="save_attendance">💾 Save Attendance</button>
    </form>
    <a href="teacher_dashboard.php">⬅ Back</a>
</div>
</body>
</html>