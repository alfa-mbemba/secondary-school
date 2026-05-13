<?php 
include 'config.php'; 
if(!isset($_SESSION['admin'])) header('Location: index.php');

$message = '';
$error = '';

// Handle password reset by admin
if(isset($_POST['reset_password'])){
    $teacher_id = $_POST['teacher_id'];
    $new_password = $_POST['new_password'];
    
    if(strlen($new_password) < 6){
        $error = "Password must be at least 6 characters!";
    } else {
        $hashed = md5($new_password);
        $conn->query("UPDATE teachers SET password = '$hashed', last_password_change = NOW() WHERE id = $teacher_id");
        $message = "✅ Password reset successfully for teacher!";
    }
}

// Handle add new teacher
if(isset($_POST['add_teacher'])){
    $teacher_id = $_POST['teacher_id'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $subject = $_POST['subject'];
    $class_assigned = $_POST['class_assigned'];
    $password = md5($_POST['password']);
    
    $check = $conn->query("SELECT * FROM teachers WHERE teacher_id = '$teacher_id'");
    if($check->num_rows > 0){
        $error = "❌ Teacher ID already exists!";
    } else {
        $conn->query("INSERT INTO teachers (teacher_id, full_name, email, phone, subject, class_assigned, password) 
                       VALUES ('$teacher_id', '$full_name', '$email', '$phone', '$subject', '$class_assigned', '$password')");
        $message = "✅ Teacher added successfully! Default password: " . $_POST['password'];
    }
}

// Handle delete teacher
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $conn->query("DELETE FROM teachers WHERE id = $id");
    $message = "✅ Teacher deleted successfully!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Teachers & Passwords</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background: white;
            padding: 25px;
            border-radius: 15px;
            max-width: 400px;
            width: 90%;
        }
        .reset-btn {
            background: #ffc107;
            color: #333;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .reset-btn:hover {
            background: #e0a800;
        }
        .delete-btn {
            color: #dc3545;
            margin-left: 10px;
            text-decoration: none;
        }
        .delete-btn:hover {
            text-decoration: underline;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>👨‍🏫 Manage Teachers & Passwords</h2>
    
    <?php if($message): ?>
        <div style="background:#d4edda; padding:12px; border-radius:8px; margin-bottom:15px; color:#155724;">
            <?= $message ?>
        </div>
    <?php endif; ?>
    
    <?php if($error): ?>
        <div style="background:#f8d7da; padding:12px; border-radius:8px; margin-bottom:15px; color:#721c24;">
            <?= $error ?>
        </div>
    <?php endif; ?>
    
    <!-- Add New Teacher Form -->
    <h3>➕ Add New Teacher</h3>
    <form method="POST" style="background:#f8f9fa; padding:20px; border-radius:10px; margin-bottom:30px;">
        <div class="form-grid">
            <input type="text" name="teacher_id" placeholder="Teacher ID (e.g., TCH002)" required>
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="tel" name="phone" placeholder="Phone" required>
            <input type="text" name="subject" placeholder="Subject (e.g., Mathematics)" required>
            <select name="class_assigned" required>
                <option value="">Select Class</option>
                <option>Form 1</option>
                <option>Form 2</option>
                <option>Form 3</option>
                <option>Form 4</option>
            </select>
            <input type="text" name="password" placeholder="Default Password" value="teacher123" required>
        </div>
        <button type="submit" name="add_teacher" style="margin-top:15px; background:#28a745;">➕ Add Teacher</button>
    </form>
    
    <!-- Existing Teachers List -->
    <h3>📋 Existing Teachers</h3>
    <table border="1" cellpadding="10" width="100%" style="border-collapse: collapse;">
        <thead>
            <tr style="background:#667eea; color:white;">
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Subject</th>
                <th>Class</th>
                <th>Last Password Change</th>
                <th>Actions</th>
              </tr>
        </thead>
        <tbody>
            <?php
            $teachers = $conn->query("SELECT * FROM teachers ORDER BY full_name");
            if($teachers && $teachers->num_rows > 0):
                while($teacher = $teachers->fetch_assoc()):
            ?>
                <tr>
                    <td><?= htmlspecialchars($teacher['teacher_id']) ?></td>
                    <td><?= htmlspecialchars($teacher['full_name']) ?></td>
                    <td><?= htmlspecialchars($teacher['email']) ?></td>
                    <td><?= htmlspecialchars($teacher['phone']) ?></td>
                    <td><?= htmlspecialchars($teacher['subject']) ?></td>
                    <td><?= htmlspecialchars($teacher['class_assigned']) ?></td>
                    <td><?= $teacher['last_password_change'] ? date('d M Y', strtotime($teacher['last_password_change'])) : 'Never' ?></td>
                    <td>
                        <button class="reset-btn" onclick="openResetModal('<?= $teacher['id'] ?>', '<?= htmlspecialchars($teacher['full_name']) ?>')">🔄 Reset Password</button>
                        <a href="?delete=<?= $teacher['id'] ?>" class="delete-btn" onclick="return confirm('Delete this teacher? This action cannot be undone!')">🗑️ Delete</a>
                     </td>
                 </tr>
            <?php 
                endwhile;
            else:
            ?>
                <tr>
                    <td colspan="8" style="text-align:center;">No teachers found. Add your first teacher above.</td>
                </tr>
            <?php endif; ?>
        </tbody>
     </table>
     
     <div style="margin-top: 20px;">
         <a href="index.php">⬅ Back to Dashboard</a>
     </div>
</div>

<!-- Reset Password Modal -->
<div id="resetModal" class="modal">
    <div class="modal-content">
        <h3>🔄 Reset Teacher Password</h3>
        <p id="teacherName"></p>
        <form method="POST">
            <input type="hidden" name="teacher_id" id="resetTeacherId">
            <label>New Password:</label>
            <input type="password" name="new_password" id="newPassword" required minlength="6">
            <label>Confirm Password:</label>
            <input type="password" id="confirmNewPassword" required>
            <div id="modalMatchMsg" style="font-size:12px; margin:5px 0;"></div>
            <button type="submit" name="reset_password" style="background:#28a745; margin-top:10px; width:100%;" id="resetBtn">🔄 Reset Password</button>
            <button type="button" onclick="closeModal()" style="background:#dc3545; margin-top:10px; width:100%;">Cancel</button>
        </form>
    </div>
</div>

<script>
function openResetModal(id, name) {
    document.getElementById('resetModal').style.display = 'flex';
    document.getElementById('resetTeacherId').value = id;
    document.getElementById('teacherName').innerHTML = `<strong>Teacher:</strong> ${name}`;
    document.getElementById('newPassword').value = '';
    document.getElementById('confirmNewPassword').value = '';
    document.getElementById('modalMatchMsg').innerHTML = '';
}

function closeModal() {
    document.getElementById('resetModal').style.display = 'none';
}

document.getElementById('confirmNewPassword')?.addEventListener('keyup', function() {
    let pass = document.getElementById('newPassword').value;
    let confirm = this.value;
    let msg = document.getElementById('modalMatchMsg');
    let btn = document.getElementById('resetBtn');
    
    if(pass === confirm && pass !== '') {
        msg.innerHTML = '✓ Passwords match!';
        msg.style.color = '#28a745';
        btn.disabled = false;
    } else if(confirm !== '') {
        msg.innerHTML = '✗ Passwords do not match!';
        msg.style.color = '#dc3545';
        btn.disabled = true;
    } else {
        msg.innerHTML = '';
        btn.disabled = false;
    }
});

// Close modal when clicking outside
window.onclick = function(event) {
    let modal = document.getElementById('resetModal');
    if(event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>
</body>
</html>