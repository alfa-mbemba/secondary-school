<?php 
include 'config.php'; 
if(!isset($_SESSION['admin'])) header('Location: index.php');

$message = '';
$error = '';

// Handle add schedule
if(isset($_POST['add_schedule'])){
    $exam_name = trim($_POST['exam_name']);
    $class = $_POST['class'];
    $subject = trim($_POST['subject']);
    $exam_date = $_POST['exam_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $venue = trim($_POST['venue']);
    $created_by = 1; // Admin ID
    
    $stmt = $conn->prepare("INSERT INTO exam_timetable (exam_name, class, subject, exam_date, start_time, end_time, venue, created_by) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssi", $exam_name, $class, $subject, $exam_date, $start_time, $end_time, $venue, $created_by);
    
    if($stmt->execute()){
        $message = "✅ Schedule added successfully!";
    } else {
        $error = "❌ Error: " . $stmt->error;
    }
    $stmt->close();
}

// Handle delete
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM exam_timetable WHERE id = ?");
    $stmt->bind_param("i", $id);
    if($stmt->execute()){
        $message = "✅ Schedule deleted successfully!";
    } else {
        $error = "❌ Error deleting schedule";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Exam Timetable Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .btn-submit {
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        .btn-submit:hover {
            background: #218838;
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
            background: #667eea;
            color: white;
        }
        .upcoming {
            background-color: #d4edda;
        }
        .past {
            background-color: #f8f9fa;
            color: #6c757d;
        }
        .delete-btn {
            color: #dc3545;
            text-decoration: none;
        }
        .delete-btn:hover {
            text-decoration: underline;
        }
        .message-success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .message-error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .tab-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .tab-btn {
            padding: 10px 20px;
            background: #e9ecef;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .tab-btn.active {
            background: #667eea;
            color: white;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📅 Exam Timetable Management</h2>
    
    <?php if($message): ?>
        <div class="message-success"><?= $message ?></div>
    <?php endif; ?>
    
    <?php if($error): ?>
        <div class="message-error"><?= $error ?></div>
    <?php endif; ?>
    
    <div class="tab-buttons">
        <button class="tab-btn active" onclick="showTab('add')">➕ Add New Schedule</button>
        <button class="tab-btn" onclick="showTab('upcoming')">📌 Upcoming Exams</button>
        <button class="tab-btn" onclick="showTab('past')">📋 Past Exams</button>
        <button class="tab-btn" onclick="showTab('all')">📋 All Exams</button>
    </div>
    
    <!-- Add Schedule Tab -->
    <div id="addTab" class="tab-content active">
        <div class="form-section">
            <h3>➕ Add New Exam Schedule</h3>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Exam Name *</label>
                        <input type="text" name="exam_name" placeholder="e.g., Mid Term, Final Exam" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Class *</label>
                        <select name="class" required>
                            <option value="">Select Class</option>
                            <option>Form 1</option>
                            <option>Form 2</option>
                            <option>Form 3</option>
                            <option>Form 4</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Subject *</label>
                        <input type="text" name="subject" placeholder="e.g., Mathematics, English" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Exam Date *</label>
                        <input type="date" name="exam_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Start Time *</label>
                        <input type="time" name="start_time" required>
                    </div>
                    
                    <div class="form-group">
                        <label>End Time *</label>
                        <input type="time" name="end_time" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Venue / Room</label>
                        <input type="text" name="venue" placeholder="e.g., Hall A, Room 101">
                    </div>
                </div>
                
                <button type="submit" name="add_schedule" class="btn-submit">➕ Add Schedule</button>
            </form>
        </div>
    </div>
    
    <!-- Upcoming Exams Tab -->
    <div id="upcomingTab" class="tab-content">
        <h3>📌 Upcoming Exams</h3>
        <?php
        $upcoming = $conn->query("SELECT * FROM exam_timetable WHERE exam_date >= CURDATE() ORDER BY exam_date, start_time");
        if($upcoming && $upcoming->num_rows > 0):
        ?>
            <table class="timetable-table">
                <thead>
                    <tr>
                        <th>Exam Name</th>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Venue</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($exam = $upcoming->fetch_assoc()): ?>
                        <tr class="upcoming">
                            <td><?= htmlspecialchars($exam['exam_name']) ?></td>
                            <td><?= htmlspecialchars($exam['class']) ?></td>
                            <td><?= htmlspecialchars($exam['subject']) ?></td>
                            <td><?= date('d M Y', strtotime($exam['exam_date'])) ?></td>
                            <td><?= date('h:i A', strtotime($exam['start_time'])) ?> - <?= date('h:i A', strtotime($exam['end_time'])) ?></td>
                            <td><?= htmlspecialchars($exam['venue'] ?: '-') ?></td>
                            <td><a href="?delete=<?= $exam['id'] ?>" class="delete-btn" onclick="return confirm('Delete this schedule?')">🗑️ Delete</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No upcoming exams scheduled.</p>
        <?php endif; ?>
    </div>
    
    <!-- Past Exams Tab -->
    <div id="pastTab" class="tab-content">
        <h3>📋 Past Exams</h3>
        <?php
        $past = $conn->query("SELECT * FROM exam_timetable WHERE exam_date < CURDATE() ORDER BY exam_date DESC");
        if($past && $past->num_rows > 0):
        ?>
            <table class="timetable-table">
                <thead>
                    <tr>
                        <th>Exam Name</th>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Venue</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($exam = $past->fetch_assoc()): ?>
                        <tr class="past">
                            <td><?= htmlspecialchars($exam['exam_name']) ?></td>
                            <td><?= htmlspecialchars($exam['class']) ?></td>
                            <td><?= htmlspecialchars($exam['subject']) ?></td>
                            <td><?= date('d M Y', strtotime($exam['exam_date'])) ?></td>
                            <td><?= date('h:i A', strtotime($exam['start_time'])) ?> - <?= date('h:i A', strtotime($exam['end_time'])) ?></td>
                            <td><?= htmlspecialchars($exam['venue'] ?: '-') ?></td>
                            <td><a href="?delete=<?= $exam['id'] ?>" class="delete-btn" onclick="return confirm('Delete this schedule?')">🗑️ Delete</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No past exams found.</p>
        <?php endif; ?>
    </div>
    
    <!-- All Exams Tab -->
    <div id="allTab" class="tab-content">
        <h3>📋 All Exam Schedules</h3>
        <?php
        $all = $conn->query("SELECT * FROM exam_timetable ORDER BY exam_date DESC, start_time");
        if($all && $all->num_rows > 0):
        ?>
            <table class="timetable-table">
                <thead>
                    <tr>
                        <th>Exam Name</th>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Venue</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($exam = $all->fetch_assoc()): 
                        $is_upcoming = strtotime($exam['exam_date']) >= strtotime(date('Y-m-d'));
                    ?>
                        <tr class="<?= $is_upcoming ? 'upcoming' : 'past' ?>">
                            <td><?= htmlspecialchars($exam['exam_name']) ?></td>
                            <td><?= htmlspecialchars($exam['class']) ?></td>
                            <td><?= htmlspecialchars($exam['subject']) ?></td>
                            <td><?= date('d M Y', strtotime($exam['exam_date'])) ?></td>
                            <td><?= date('h:i A', strtotime($exam['start_time'])) ?> - <?= date('h:i A', strtotime($exam['end_time'])) ?></td>
                            <td><?= htmlspecialchars($exam['venue'] ?: '-') ?></td>
                            <td><a href="?delete=<?= $exam['id'] ?>" class="delete-btn" onclick="return confirm('Delete this schedule?')">🗑️ Delete</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No exam schedules found. Click "Add New Schedule" to create one.</p>
        <?php endif; ?>
    </div>
    
    <div style="margin-top: 20px;">
        <a href="index.php">⬅ Back to Dashboard</a>
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all tabs
    document.getElementById('addTab').classList.remove('active');
    document.getElementById('upcomingTab').classList.remove('active');
    document.getElementById('pastTab').classList.remove('active');
    document.getElementById('allTab').classList.remove('active');
    
    // Show selected tab
    if(tabName === 'add') document.getElementById('addTab').classList.add('active');
    else if(tabName === 'upcoming') document.getElementById('upcomingTab').classList.add('active');
    else if(tabName === 'past') document.getElementById('pastTab').classList.add('active');
    else if(tabName === 'all') document.getElementById('allTab').classList.add('active');
    
    // Update button styles
    let buttons = document.querySelectorAll('.tab-btn');
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
}
</script>
</body>
</html>