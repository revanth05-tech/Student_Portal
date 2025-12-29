<?php
include "db.php";

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$id = $_GET['id'];

/* UPDATE */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $year = trim($_POST['year']); // IMPORTANT

    $stmt = $conn->prepare(
        "UPDATE student 
         SET Name=?, College=?, Branch=?, year=? 
         WHERE college_id=?"
    );

    $stmt->bind_param(
        "sssss",
        $_POST['name'],
        $_POST['college'],
        $_POST['branch'],
        $year,
        $id
    );

    $stmt->execute();

    header("Location: dashboard.php");
    exit;
}

/* FETCH */
$stmt = $conn->prepare(
    "SELECT Name, College, Branch, year FROM student WHERE college_id=?"
);
$stmt->bind_param("s", $id);
$stmt->execute();
$s = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
<title>Update Student</title>
<style>
body{font-family:Arial;background:#eef}
.box{width:400px;margin:80px auto;padding:30px;background:#fff;border-radius:10px}
input,button{width:100%;padding:10px;margin:8px 0}
button{background:green;color:#fff;border:none}
</style>
</head>
<body>

<div class="box">
<h2>Update Student Details</h2>

<form method="post">
    <input name="name" value="<?= htmlspecialchars($s['Name']) ?>" required>
    <input name="college" value="<?= htmlspecialchars($s['College']) ?>" required>
    <input name="branch" value="<?= htmlspecialchars($s['Branch']) ?>" required>
    <input name="year"
           value="<?= htmlspecialchars($s['year'] ?? '') ?>"
           placeholder="Year (IV-II)" required>
    <button>Update</button>
    <button>+</button>
</form>

</div>
</body>
</html>
