<?php
include "db.php";
$id=$_GET['id'];
$conn->query("DELETE FROM student WHERE college_id='$id'");
header("Location: dashboard.php");
