<?php 
include 'config.php'; 
if(!isset($_SESSION['admin'])) header('Location: index.php');

// Get all students without parent accounts
$students_without_parent = $conn->query("
    SELECT s.* FROM students s 
    LEFT JOIN parents p ON s.admission_no = p.student_admission_no 
    WHERE p.id IS NULL
");

// Handle parent account creation
if(isset($_POST['create_parent'])){
    $admission_no = $_POST['admission_no'];
    $parent_name = $_POST['parent_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = md5($_POST['password']);
    
    $check = $conn->query("SELECT * FROM parents WHERE student_admission_no = '$admission_no'");
    if($check->num_rows == 0){
        $conn->query("INSERT INTO parents (username, full_name, email, phone, student_admission_no, password) 
                       VALUES ('$admission_no', '$parent_name', '$email', '$phone', '$admission_no', '$password')");
        echo "<script>alert('✅ Parent account created!'); window.location.href='create_parent_account.php';</script>";
    } else {
        echo "<script>alert('❌ Parent account already exists for this student!');</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Parent Account</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>👪 Create Parent Account</h2>
    
    <form method="POST">
        <label>Select Student:</label>
        <select name="admission_no" required>
            <option value="">-- Select Student --</option>
            <?php while($student = $students_without_parent->fetch_assoc()): ?>
                <option value="<?= $student['admission_no'] ?>">
                    <?= $student['admission_no'] ?> - <?= $student['full_name'] ?> (<?= $student['class'] ?>)
                </option>
            <?php endwhile; ?>
        </select>
        
        <input type="text" name="parent_name" placeholder="Parent Full Name" required>
        <input type="email" name="email" placeholder="Parent Email" required>
        <input type="tel" name="phone" placeholder="Parent Phone Number" required>
        <input type="text" name="password" placeholder="Parent Password" value="parent123" required>
        
        <button type="submit" name="create_parent">➕ Create Parent Account</button>
    </form>
    
    <h3>📋 Existing Parent Accounts</h3>
    <table border="1" cellpadding="8" width="100%">
        <tr><th>Student</th><th>Parent Name</th><th>Email</th><th>Phone</th><th>Login ID</th></tr>
        <?php
        $parents = $conn->query("
            SELECT p.*, s.full_name as student_name, s.admission_no 
            FROM parents p 
            JOIN students s ON p.student_admission_no = s.admission_no
        ");
        while($parent = $parents->fetch_assoc()):
        ?>
            <tr>
                <td><?= $parent['student_name'] ?> (<?= $parent['admission_no'] ?>)</td>
                <td><?= $parent['full_name'] ?></td>
                <td><?= $parent['email'] ?></td>
                <td><?= $parent['phone'] ?></td>
                <td><strong><?= $parent['admission_no'] ?></strong></td>
            </tr>
        <?php endwhile; ?>
    </table>
    
    <a href="index.php">⬅ Back to Admin Dashboard</a>
</div>
</body>
</html>