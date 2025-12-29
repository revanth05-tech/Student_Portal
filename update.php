<?php
include "db.php";
$id = $_GET['id'];

$s = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM student WHERE id='$id'"));

if (isset($_POST['update'])) {
    mysqli_query($conn,
        "UPDATE student SET
         Name='{$_POST['name']}',
         Branch='{$_POST['branch']}',
         year='{$_POST['year']}'
         WHERE id='$id'"
    );
    header("Location: dashboard.php");
}
?>

<!DOCTYPE html>
<html>
<head><title>Update</title></head>
<body>
<div class="box">
<h2>Update Student</h2>
<form method="post">
<input name="name" value="<?= $s['Name'] ?>">
<input name="branch" value="<?= $s['Branch'] ?>">
<input name="year" value="<?= $s['year'] ?>">
<button name="update">Update</button>
</form>
</div>
</body>
</html>
