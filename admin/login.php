<?php
session_start();
if (isset($_POST['login'])) {
    $_SESSION['admin'] = true;
    header("Location: dashboard.php");
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body class="auth">

<form method="POST" class="card">
<h2>Admin Login</h2>
<input placeholder="Email" required>
<input type="password" placeholder="Password" required>
<button name="login">Login</button>
</form>

</body>
</html>
