<?php include "db.php";
if($_POST){
$q=$conn->prepare(
"INSERT INTO student(college_id,Name,College,Branch,year)
VALUES(?,?,?,?,?)");
$q->bind_param("sssss",
$_POST['college_id'],$_POST['name'],$_POST['college'],
$_POST['branch'],$_POST['year']);
$q->execute();
header("Location: dashboard.php");
}
?>
<form method="post">
<input name="college_id" placeholder="College ID">
<input name="name" placeholder="Name">
<input name="college" placeholder="College">
<input name="branch" placeholder="Branch">
<input name="year" placeholder="Year (IV-II)">
<button>Add</button>
</form>
