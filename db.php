<?php
$conn = new mysqli("localhost", "root", "", "student_portal_db");
if ($conn->connect_error) {
    die("Connection failed");
}
?>
