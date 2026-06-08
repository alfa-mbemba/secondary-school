<?php 
include 'config.php'; 
if(!isset($_SESSION['parent_logged_in'])) header('Location: parent_login.php');

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];
$admission_no = $_SESSION['student_admission_no'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Attendance - <?= $student_name ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .attendance-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        .stat-box {
            background: white;
            padding: 15px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .stat-box.present { border-top: 4px solid #28a745; }
        .stat-box.absent { border-top: 4px solid #dc3545; }
        .stat-box.late { border-top: 4px solid #ffc107; }
        .stat-box.percentage { border-top: 4px solid #667eea; }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
        }
        .calendar-view {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
            margin-top: 20px;
        }
        .calendar-day {
            background: #f8f9fa;
            padding: 12px;
            text-align: center;
            border-radius: 10px;
            font-size: 13px;
        }
        .calendar-day.present { background: #d4edda; color: #155724; }
        .calendar-day.absent { background: #f8d7da; color: #721c24; }
        .calendar-day.late { background: #fff3cd; color: #856404; }
        .calendar-day.empty { background: #e9ecef; color: #6c757d; }
        .month-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📋 Attendance Record</h2>
    
    <div style="background:#f0f4ff; padding:15px; border-radius:10px; margin-bottom:20px;">
        <p><strong>Student:</strong> <?= htmlspecialchars($student_name) ?> (<?= $admission_no ?>)</p>
        <p><strong>Class:</strong> <?= $_SESSION['student_class'] ?></p>
    </div>
    
    <?php
    // Get current month or selected month
    $month = $_GET['month'] ?? date('m');
    $year = $_GET['year'] ?? date('Y');
    $selected_date = "$year-$month-01";
    
    // Get statistics for current month
    $stats = $conn->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) as present,
            SUM(CASE WHEN status='Absent' THEN 1 ELSE 0 END) as absent,
            SUM(CASE WHEN status='Late' THEN 1 ELSE 0 END) as late
        FROM attendance 
        WHERE student_id = $student_id 
        AND MONTH(date) = $month AND YEAR(date) = $year
    ")->fetch_assoc();
    
    $total_days = $stats['total'] ?? 0;
    $present = $stats['present'] ?? 0;
    $absent = $stats['absent'] ?? 0;
    $late = $stats['late'] ?? 0;
    $percentage = $total_days > 0 ? round(($present / $total_days) * 100, 1) : 0;
    ?>
    
    <!-- Month Selector -->
    <div class="month-nav">
        <a href="?month=<?= $month-1 < 1 ? 12 : $month-1 ?>&year=<?= $month-1 < 1 ? $year-1 : $year ?>" class="no-print">◀ Previous Month</a>
        <h3><?= date('F Y', strtotime($selected_date)) ?></h3>
        <a href="?month=<?= $month+1 > 12 ? 1 : $month+1 ?>&year=<?= $month+1 > 12 ? $year+1 : $year ?>" class="no-print">Next Month ▶</a>
    </div>
    
    <!-- Statistics Cards -->
    <div class="attendance-stats">
        <div class="stat-box present">
            <div class="stat-number" style="color:#28a745;">✅ <?= $present ?></div>
            <small>Present Days</small>
        </div>
        <div class="stat-box absent">
            <div class="stat-number" style="color:#dc3545;">❌ <?= $absent ?></div>
            <small>Absent Days</small>
        </div>
        <div class="stat-box late">
            <div class="stat-number" style="color:#ffc107;">⏰ <?= $late ?></div>
            <small>Late Days</small>
        </div>
        <div class="stat-box percentage">
            <div class="stat-number" style="color:#667eea;"><?= $percentage ?>%</div>
            <small>Attendance Rate</small>
        </div>
    </div>
    
    <!-- Calendar View -->
    <h4>📅 Daily Attendance Calendar</h4>
    <div class="calendar-view">
        <div class="calendar-day"><strong>Sun</strong></div>
        <div class="calendar-day"><strong>Mon</strong></div>
        <div class="calendar-day"><strong>Tue</strong></div>
        <div class="calendar-day"><strong>Wed</strong></div>
        <div class="calendar-day"><strong>Thu</strong></div>
        <div class="calendar-day"><strong>Fri</strong></div>
        <div class="calendar-day"><strong>Sat</strong></div>
        
        <?php
        // Get attendance data for the month
        $attendance_data = [];
        $att_records = $conn->query("SELECT date, status FROM attendance WHERE student_id = $student_id AND MONTH(date) = $month AND YEAR(date) = $year");
        while($rec = $att_records->fetch_assoc()){
            $attendance_data[$rec['date']] = $rec['status'];
        }
        
        $first_day = strtotime($selected_date);
        $days_in_month = date('t', $first_day);
        $start_offset = date('w', $first_day);
        
        for($i = 0; $i < $start_offset; $i++){
            echo '<div class="calendar-day empty"></div>';
        }
        
        for($day = 1; $day <= $days_in_month; $day++){
            $date_str = "$year-$month-" . str_pad($day, 2, '0', STR_PAD_LEFT);
            $status = $attendance_data[$date_str] ?? '';
            $status_class = strtolower($status);
            echo "<div class='calendar-day $status_class'>";
            echo "<strong>$day</strong><br>";
            echo $status ?: '-';
            echo "</div>";
        }
        ?>
    </div>
    
    <!-- Detailed Table View -->
    <h4 style="margin-top: 30px;">📋 Detailed Attendance Log</h4>
    <table border="1" cellpadding="10" width="100%">
        <thead>
            <tr style="background:#667eea; color:white;">
                <th>Date</th>
                <th>Day</th>
                <th>Status</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $details = $conn->query("SELECT * FROM attendance WHERE student_id = $student_id AND MONTH(date) = $month AND YEAR(date) = $year ORDER BY date DESC");
            if($details->num_rows > 0):
                while($row = $details->fetch_assoc()):
                    $status_class = strtolower($row['status']);
            ?>
                <tr>
                    <td><?= date('d M Y', strtotime($row['date'])) ?></td>
                    <td><?= date('l', strtotime($row['date'])) ?></td>
                    <td class="<?= $status_class ?>" style="font-weight:bold;"><?= $row['status'] ?></td>
                    <td><?= $row['remark'] ?: '-' ?></td>
                </tr>
            <?php 
                endwhile;
            else:
            ?>
                <td><td colspan="4">No attendance records for this month.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div class="no-print" style="margin-top: 20px;">
        <a href="parent_dashboard.php">⬅ Back to Dashboard</a>
        <button onclick="window.print()" style="float:right; background:#28a745;">🖨️ Print Attendance</button>
    </div>
</div>
</body>
</html>