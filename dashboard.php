<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

include "db.php";

$student = null;

if (isset($_POST['search'])) {
    $college_id = trim($_POST['college_id']);

    $stmt = $conn->prepare(
        "SELECT college_id, Name, College, Branch, year 
         FROM student 
         WHERE college_id = ?"
    );
    $stmt->bind_param("s", $college_id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Dashboard</title>
<style>
body{
    font-family: Arial;
    background:#eef;
}
.box{
    width:450px;
    margin:60px auto;
    padding:30px;
    background:#fff;
    border-radius:10px;
}
input,button{
    width:100%;
    padding:10px;
    margin:8px 0;
}
.blue{background:#2b7cff;color:#fff;border:none}
.green{background:green;color:#fff;border:none}
.red{background:red;color:#fff;border:none}
.gray{background:#ddd}
</style>
</head>
<body>

<div class="box">
<h2>Student Details</h2>

<form method="post">
    <input type="text" name="college_id" placeholder="Enter College ID" required>
    <button class="blue" name="search">Get Details</button>
</form>

<a href="add_student.php">
    <button class="green">Add New Student</button>
</a>

<?php if ($student): ?>
<hr>
<p><b>ID:</b> <?= htmlspecialchars($student['college_id']) ?></p>
<p><b>Name:</b> <?= htmlspecialchars($student['Name']) ?></p>
<p><b>College:</b> <?= htmlspecialchars($student['College']) ?></p>
<p><b>Branch:</b> <?= htmlspecialchars($student['Branch']) ?></p>
<p><b>Year:</b>
    <?= (!empty($student['year'])) 
        ? htmlspecialchars($student['year']) 
        : 'Not Updated'; ?>
</p>

<a href="update_student.php?id=<?= urlencode($student['college_id']) ?>">
    <button class="green">Edit</button>
</a>

<a href="delete_student.php?id=<?= urlencode($student['college_id']) ?>"
   onclick="return confirm('Are you sure?')">
    <button class="red">Delete</button>
</a>
<?php endif; ?>

<a href="logout.php">
    <button class="gray">Logout</button>
</a>

</div>
</body>
</html>
