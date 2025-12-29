<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<style>
body{font-family:Arial;background:#eef}
.box{width:350px;margin:100px auto;padding:30px;background:#fff;border-radius:8px}
input,button{width:100%;padding:10px;margin:10px 0}
button{background:#2b7cff;color:#fff;border:none}
</style>
</head>
<body>
<div class="box">
<h2>Student Portal Login</h2>
<form method="post" action="login_check.php">
<input type="text" name="username" placeholder="Username" required>
<input type="password" name="password" placeholder="Password" required>
<button>Login</button>
</form>
</div>
</body>
</html>
