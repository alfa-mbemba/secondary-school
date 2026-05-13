<?php include 'config.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Exam Timetable</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>📅 Exam Timetable</h2>
    
    <form method="GET" style="margin:15px 0;">
        <select name="class">
            <option value="">All Classes</option>
            <option>Form 1</option><option>Form 2</option><option>Form 3</option><option>Form 4</option>
        </select>
        <button type="submit">Filter</button>
    </form>
    
    <?php
    $class_filter = isset($_GET['class']) && $_GET['class'] != '' ? "WHERE class = '{$_GET['class']}'" : "";
    $timetable = $conn->query("SELECT * FROM exam_timetable $class_filter ORDER BY exam_date, start_time");
    
    if($timetable->num_rows > 0):
        $current_exam = '';
        while($exam = $timetable->fetch_assoc()):
            if($current_exam != $exam['exam_name']):
                if($current_exam != '') echo '</tbody></table>';
                $current_exam = $exam['exam_name'];
    ?>
                <h3>📚 <?= $exam['exam_name'] ?> (<?= $exam['class'] ?>)</h3>
                <table border="1" cellpadding="8" width="100%">
                    <tr><th>Subject</th><th>Date</th><th>Time</th><th>Venue</th></tr>
    <?php endif; ?>
                <tr>
                    <td><?= $exam['subject'] ?></td>
                    <td><?= date('d M Y', strtotime($exam['exam_date'])) ?></td>
                    <td><?= date('h:i A', strtotime($exam['start_time'])) ?> - <?= date('h:i A', strtotime($exam['end_time'])) ?></td>
                    <td><?= $exam['venue'] ?></td>
                </tr>
    <?php 
        endwhile;
        echo '</tbody></table>';
    else:
    ?>
        <p>No exam schedules available.</p>
    <?php endif; ?>
    
    <a href="javascript:history.back()">⬅ Back</a>
</div>
</body>
</html>