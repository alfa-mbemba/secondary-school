<?php include 'config.php'; if(!isset($_SESSION['admin'])) header('Location: index.php'); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Teachers</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>👨‍🏫 Manage Teachers</h2>
    
    <h3>➕ Add New Teacher</h3>
    <form method="POST">
        <input type="text" name="teacher_id" placeholder="Teacher ID (e.g., TCH002)" required>
        <input type="text" name="full_name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="phone" placeholder="Phone">
        <input type="text" name="subject" placeholder="Subject" required>
        <select name="class_assigned">
            <option>Form 1</option><option>Form 2</option><option>Form 3</option><option>Form 4</option>
        </select>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="add_teacher">Add Teacher</button>
    </form>
    
    <?php
    if(isset($_POST['add_teacher'])){
        $tid = $_POST['teacher_id'];
        $name = $_POST['full_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $subject = $_POST['subject'];
        $class = $_POST['class_assigned'];
        $pass = md5($_POST['password']);
        
        $conn->query("INSERT INTO teachers (teacher_id, full_name, email, phone, subject, class_assigned, password) 
                       VALUES ('$tid', '$name', '$email', '$phone', '$subject', '$class', '$pass')");
        echo "<p style='color:green'>✅ Teacher Added Successfully</p>";
    }
    ?>
    
    <h3>📋 Existing Teachers</h3>
    <table border="1" cellpadding="8">
        <tr>
            <th>ID</th><th>Name</th><th>Subject</th><th>Class</th><th>Email</th><th>Action</th>
        </tr>
        <?php
        $teachers = $conn->query("SELECT * FROM teachers");
        while($t = $teachers->fetch_assoc()):
        ?>
        <tr>
            <td><?= $t['teacher_id'] ?></td>
            <td><?= $t['full_name'] ?></td>
            <td><?= $t['subject'] ?></td>
            <td><?= $t['class_assigned'] ?></td>
            <td><?= $t['email'] ?></td>
            <td>
                <a href="delete_teacher.php?id=<?= $t['id'] ?>" onclick="return confirm('Delete this teacher?')">🗑️ Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <a href="index.php">⬅ Back</a>
</div>
</body>
</html>