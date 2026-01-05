<?php
session_start();

if (isset($_POST['login'])) {
    // mark admin as logged in
    $_SESSION['admin_id'] = 1;

    header("Location: dashboard.php");
    exit;
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

<input name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>

<button type="submit" name="login">Login</button>
</form>

</body>
</html>
