<?php
session_start();
include "db.php";

$user = $_POST['username'];
$pass = $_POST['password'];

$q = $conn->prepare("SELECT * FROM login WHERE username=? AND password=?");
$q->bind_param("ss",$user,$pass);
$q->execute();
$r = $q->get_result();

if($r->num_rows==1){
    $_SESSION['admin']=true;
    header("Location: dashboard.php");
}else{
    echo "Invalid login";
}
