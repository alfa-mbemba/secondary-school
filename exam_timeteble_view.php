<?php 
include 'config.php'; 

// Check if user is logged in (teacher or parent)
if(!isset($_SESSION['teacher']) && !isset($_SESSION['parent_logged_in'])){
    header('Location: index.php');
    exit();
}

// Get class from session
if(isset($_SESSION['teacher'])){
    $class = $_SESSION['teacher_data']['class_assigned'];
    $user_type = 'teacher';
    $user_name = $_SESSION['teacher_data']['full_name'];
} else {
    $class = $_SESSION['student_class'];
    $user_type = 'parent';
    $user_name = $_SESSION['parent_name'];
}

// Get filter parameters
$filter_class = isset($_GET['class']) ? $_GET['class'] : $class;
$filter_type = isset($_GET['type']) ? $_GET['type'] : 'upcoming';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Exam Timetable</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .timetable-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .timetable-header {
            background: #667eea;
            color: white;
            padding: 15px 20px;
            font-size: 18px;
            font-weight: bold;
        }
        .upcoming-badge {
            background: #28a745;
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            margin-left: 10px;
        }
        .filter-bar {
            background: #f0f4ff;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        .filter-bar select, .filter-bar button {
            padding: 8px 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .timetable-table {
            width: 100%;
            border-collapse: collapse;
        }
        .timetable-table th, .timetable-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .timetable-table th {
            background: #f0f4ff;
        }
        .upcoming-row {
            background-color: #d4edda;
        }
        @media print {
            .no-print { display: none; }
            .container { padding: 0; }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📅 Exam Timetable</h2>
    
    <div style="background:#f0f4ff; padding:15px; border-radius:10px; margin-bottom:20px;">
        <p><strong><?= ucfirst($user_type) ?>:</strong> <?= htmlspecialchars($user_name) ?></p>
        <p><strong>Class:</strong> <?= htmlspecialchars($class) ?></p>
    </div>
    
    <div class="filter-bar no-print">
        <select id="classFilter" onchange="filterTimetable()">
            <option value="">All Classes</option>
            <option value="Form 1" <?= ($filter_class == 'Form 1') ? 'selected' : '' ?>>Form 1</option>
            <option value="Form 2" <?= ($filter_class == 'Form 2') ? 'selected' : '' ?>>Form 2</option>
            <option value="Form 3" <?= ($filter_class == 'Form 3') ? 'selected' : '' ?>>Form 3</option>
            <option value="Form 4" <?= ($filter_class == 'Form 4') ? 'selected' : '' ?>>Form 4</option>
        </select>
        
        <select id="typeFilter" onchange="filterTimetable()">
            <option value="upcoming" <?= ($filter_type == 'upcoming') ? 'selected' : '' ?>>📌 Upcoming Exams</option>
            <option value="past" <?= ($filter_type == 'past') ? 'selected' : '' ?>>📋 Past Exams</option>
            <option value="all" <?= ($filter_type == 'all') ? 'selected' : '' ?>>📚 All Exams</option>
        </select>
        
        <button onclick="window.print()">🖨️ Print</button>
    </div>
    
    <div id="timetableContent">
        <?php
        // Build query based on filters
        $where = "1=1";
        if($filter_class != ''){
            $where .= " AND class = '$filter_class'";
        }
        if($filter_type == 'upcoming'){
            $where .= " AND exam_date >= CURDATE()";
            $order = "ORDER BY exam_date, start_time";
        } elseif($filter_type == 'past'){
            $where .= " AND exam_date < CURDATE()";
            $order = "ORDER BY exam_date DESC";
        } else {
            $order = "ORDER BY exam_date DESC, start_time";
        }
        
        $timetable = $conn->query("SELECT * FROM exam_timetable WHERE $where $order");
        
        if($timetable && $timetable->num_rows > 0):
            $current_exam = '';
            while($exam = $timetable->fetch_assoc()):
                $is_upcoming = strtotime($exam['exam_date']) >= strtotime(date('Y-m-d'));
        ?>
                <div class="timetable-card">
                    <div class="timetable-header">
                        <?= htmlspecialchars($exam['exam_name']) ?> 
                        (<?= htmlspecialchars($exam['class']) ?>)
                        <?php if($is_upcoming && $filter_type != 'past'): ?>
                            <span class="upcoming-badge">Upcoming</span>
                        <?php endif; ?>
                    </div>
                    <table class="timetable-table">
                        <thead>
                            <tr><th>Subject</th><th>Date</th><th>Time</th><th>Venue</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?= htmlspecialchars($exam['subject']) ?></td>
                                <td><?= date('l, d M Y', strtotime($exam['exam_date'])) ?></td>
                                <td><?= date('h:i A', strtotime($exam['start_time'])) ?> - <?= date('h:i A', strtotime($exam['end_time'])) ?></td>
                                <td><?= htmlspecialchars($exam['venue'] ?: 'TBA') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
        <?php 
            endwhile;
        else:
        ?>
            <div style="background:#f8d7da; padding:20px; text-align:center; border-radius:10px;">
                ❌ No exam schedules found for the selected criteria.
            </div>
        <?php endif; ?>
    </div>
    
    <div class="no-print" style="margin-top: 20px;">
        <a href="<?= isset($_SESSION['teacher']) ? 'teacher_dashboard.php' : 'parent_dashboard.php' ?>">⬅ Back to Dashboard</a>
    </div>
</div>

<script>
function filterTimetable() {
    let classFilter = document.getElementById('classFilter').value;
    let typeFilter = document.getElementById('typeFilter').value;
    window.location.href = 'exam_timetable_view.php?class=' + classFilter + '&type=' + typeFilter;
}
</script>
</body>
</html>