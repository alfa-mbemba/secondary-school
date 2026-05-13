<?php 
include 'config.php'; 
if(!isset($_SESSION['teacher'])) header('Location: teacher_login.php');
$teacher = $_SESSION['teacher_data'];
$class = $teacher['class_assigned'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Attendance - <?= $class ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .present { color: green; font-weight: bold; }
        .absent { color: red; font-weight: bold; }
        .late { color: orange; font-weight: bold; }
        .summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            flex: 1;
            margin: 5px;
        }
        .summary-container {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .calendar-view {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
            margin-top: 20px;
        }
        .calendar-day {
            background: #f0f4ff;
            padding: 10px;
            text-align: center;
            border-radius: 8px;
            font-size: 12px;
        }
        .calendar-day.present { background: #d4edda; }
        .calendar-day.absent { background: #f8d7da; }
        .calendar-day.late { background: #fff3cd; }
    </style>
</head>
<body>
<div class="container">
    <h2>📊 Attendance Report - <?= $class ?></h2>
    
    <div class="filter-bar">
        <form method="GET">
            <input type="date" name="from_date" value="<?= $_GET['from_date'] ?? date('Y-m-01') ?>" required>
            <input type="date" name="to_date" value="<?= $_GET['to_date'] ?? date('Y-m-t') ?>" required>
            <select name="student_id">
                <option value="">-- All Students --</option>
                <?php
                $students = $conn->query("SELECT id, admission_no, full_name FROM students WHERE class='$class'");
                while($student = $students->fetch_assoc()){
                    $selected = (isset($_GET['student_id']) && $_GET['student_id'] == $student['id']) ? 'selected' : '';
                    echo "<option value='{$student['id']}' $selected>{$student['admission_no']} - {$student['full_name']}</option>";
                }
                ?>
            </select>
            <button type="submit">🔍 Show Report</button>
            <button type="button" onclick="window.print()" style="background:#17a2b8;">🖨️ Print</button>
        </form>
    </div>
    
    <?php
    if(isset($_GET['from_date']) && isset($_GET['to_date'])){
        $from = $_GET['from_date'];
        $to = $_GET['to_date'];
        $student_filter = isset($_GET['student_id']) && $_GET['student_id'] != '' ? "AND student_id = {$_GET['student_id']}" : "";
        
        // Overall statistics
        $stats = $conn->query("
            SELECT 
                COUNT(DISTINCT student_id) as total_students,
                COUNT(*) as total_records,
                SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN status='Absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN status='Late' THEN 1 ELSE 0 END) as late_count
            FROM attendance a
            JOIN students s ON a.student_id = s.id
            WHERE s.class = '$class' AND date BETWEEN '$from' AND '$to' $student_filter
        ")->fetch_assoc();
        
        $total = $stats['total_records'] ?? 0;
        $present_rate = $total > 0 ? round(($stats['present_count'] / $total) * 100, 1) : 0;
    ?>
    
    <div class="summary-container">
        <div class="summary-card">
            <h3><?= $stats['total_students'] ?? 0 ?></h3>
            <p>Students</p>
        </div>
        <div class="summary-card">
            <h3 class="present">✅ <?= $stats['present_count'] ?? 0 ?></h3>
            <p>Present Days</p>
        </div>
        <div class="summary-card">
            <h3 class="absent">❌ <?= $stats['absent_count'] ?? 0 ?></h3>
            <p>Absent Days</p>
        </div>
        <div class="summary-card">
            <h3 class="late">⏰ <?= $stats['late_count'] ?? 0 ?></h3>
            <p>Late Days</p>
        </div>
        <div class="summary-card">
            <h3><?= $present_rate ?>%</h3>
            <p>Attendance Rate</p>
        </div>
    </div>
    
    <!-- Individual Student Attendance Table -->
    <table border="1" cellpadding="10" cellspacing="0" width="100%">
        <thead>
            <tr style="background:#667eea; color:white;">
                <th>Admission No</th>
                <th>Student Name</th>
                <th>Present</th>
                <th>Absent</th>
                <th>Late</th>
                <th>Total Days</th>
                <th>Attendance %</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $students = $conn->query("SELECT id, admission_no, full_name FROM students WHERE class='$class'");
            while($student = $students->fetch_assoc()):
                $student_stats = $conn->query("
                    SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) as present,
                        SUM(CASE WHEN status='Absent' THEN 1 ELSE 0 END) as absent,
                        SUM(CASE WHEN status='Late' THEN 1 ELSE 0 END) as late
                    FROM attendance 
                    WHERE student_id = {$student['id']} AND date BETWEEN '$from' AND '$to'
                ")->fetch_assoc();
                
                $total_days = $student_stats['total'] ?? 0;
                $present = $student_stats['present'] ?? 0;
                $percentage = $total_days > 0 ? round(($present / $total_days) * 100, 1) : 0;
                $status_class = $percentage >= 75 ? 'present' : ($percentage >= 50 ? 'late' : 'absent');
                $status_text = $percentage >= 75 ? 'Good' : ($percentage >= 50 ? 'Warning' : 'Critical');
            ?>
                <tr>
                    <td><?= $student['admission_no'] ?></td>
                    <td><?= $student['full_name'] ?></td>
                    <td class="present"><?= $student_stats['present'] ?? 0 ?></td>
                    <td class="absent"><?= $student_stats['absent'] ?? 0 ?></td>
                    <td class="late"><?= $student_stats['late'] ?? 0 ?></td>
                    <td><?= $total_days ?></td>
                    <td class="<?= $status_class ?>"><?= $percentage ?>%</td>
                    <td class="<?= $status_class ?>"><?= $status_text ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <!-- Calendar View for Selected Student -->
    <?php if(isset($_GET['student_id']) && $_GET['student_id'] != ''): 
        $selected_student = $conn->query("SELECT full_name FROM students WHERE id={$_GET['student_id']}")->fetch_assoc();
        $daily_attendance = $conn->query("
            SELECT date, status FROM attendance 
            WHERE student_id = {$_GET['student_id']} AND date BETWEEN '$from' AND '$to'
            ORDER BY date
        ");
    ?>
        <h3>📅 Daily Attendance: <?= $selected_student['full_name'] ?></h3>
        <div class="calendar-view">
            <?php 
            $attendance_map = [];
            while($row = $daily_attendance->fetch_assoc()){
                $attendance_map[$row['date']] = $row['status'];
            }
            
            $start = new DateTime($from);
            $end = new DateTime($to);
            $end->modify('+1 day');
            $interval = new DateInterval('P1D');
            $date_range = new DatePeriod($start, $interval, $end);
            
            foreach($date_range as $date):
                $date_str = $date->format('Y-m-d');
                $status = $attendance_map[$date_str] ?? 'Not Marked';
                $status_class = strtolower(str_replace(' ', '', $status));
            ?>
                <div class="calendar-day <?= $status_class ?>">
                    <strong><?= $date->format('d M') ?></strong><br>
                    <?= $status ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php } ?>
    
    <div style="margin-top: 20px;">
        <a href="teacher_dashboard.php">⬅ Back to Dashboard</a>
    </div>
</div>
</body>
</html>