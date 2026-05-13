<?php include 'config.php'; if(!isset($_SESSION['admin'])) header('Location: index.php'); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Exam Timetable</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>📅 Exam Timetable Management</h2>
    
    <h3>➕ Add New Exam Schedule</h3>
    <form method="POST">
        <input type="text" name="exam_name" placeholder="Exam Name" required>
        <select name="class" required>
            <option>Form 1</option><option>Form 2</option><option>Form 3</option><option>Form 4</option>
        </select>
        <input type="text" name="subject" placeholder="Subject" required>
        <input type="date" name="exam_date" required>
        <input type="time" name="start_time" required>
        <input type="time" name="end_time" required>
        <input type="text" name="venue" placeholder="Venue/Room">
        <button type="submit" name="add_schedule">Add Schedule</button>
    </form>
    
    <?php
    if(isset($_POST['add_schedule'])){
        $conn->query("INSERT INTO exam_timetable (exam_name, class, subject, exam_date, start_time, end_time, venue, created_by) 
                       VALUES ('{$_POST['exam_name']}', '{$_POST['class']}', '{$_POST['subject']}', '{$_POST['exam_date']}', 
                               '{$_POST['start_time']}', '{$_POST['end_time']}', '{$_POST['venue']}', 1)");
        echo "<p style='color:green'>✅ Schedule added!</p>";
    }
    ?>
    
    <h3>📋 Upcoming Exams</h3>
    <?php
    $timetable = $conn->query("SELECT * FROM exam_timetable WHERE exam_date >= CURDATE() ORDER BY exam_date, start_time");
    if($timetable->num_rows > 0):
    ?>
        <table border="1" cellpadding="8" width="100%">
            <tr><th>Exam</th><th>Class</th><th>Subject</th><th>Date</th><th>Time</th><th>Venue</th><th>Action</th></tr>
            <?php while($exam = $timetable->fetch_assoc()): ?>
            <tr>
                <td><?= $exam['exam_name'] ?></td>
                <td><?= $exam['class'] ?></td>
                <td><?= $exam['subject'] ?></td>
                <td><?= $exam['exam_date'] ?></td>
                <td><?= date('h:i A', strtotime($exam['start_time'])) ?> - <?= date('h:i A', strtotime($exam['end_time'])) ?></td>
                <td><?= $exam['venue'] ?></td>
                <td><a href="delete_timetable.php?id=<?= $exam['id'] ?>" onclick="return confirm('Delete?')">🗑️</a></td>
            </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No upcoming exams scheduled.</p>
    <?php endif; ?>
    
    <a href="index.php">⬅ Back to Admin</a>
</div>
</body>
</html>