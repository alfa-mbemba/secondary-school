<?php include 'config.php'; if(!isset($_SESSION['admin'])) header('Location: index.php'); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Student</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>➕ Register New Student</h2>
    <form method="POST">
        <input type="text" name="admission_no" placeholder="Admission Number" required>
        <input type="text" name="full_name" placeholder="Full Name" required>
        <select name="class">
            <option>Form 1</option><option>Form 2</option><option>Form 3</option><option>Form 4</option>
        </select>
        <input type="text" name="parent_phone" placeholder="Parent Phone">
        <textarea name="address" placeholder="Address"></textarea>
        <button type="submit" name="submit">Save Student</button>
    </form>
    <?php
    if(isset($_POST['submit'])){
        $ad = $_POST['admission_no'];
        $name = $_POST['full_name'];
        $class = $_POST['class'];
        $phone = $_POST['parent_phone'];
        $addr = $_POST['address'];
        $conn->query("INSERT INTO students (admission_no, full_name, class, parent_phone, address) VALUES ('$ad','$name','$class','$phone','$addr')");
        echo "<p style='color:green'>✅ Student Added Successfully</p>";
    }
    ?>
    <a href="index.php">⬅ Back</a>
</div>
</body>
</html>