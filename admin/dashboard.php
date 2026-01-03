<?php
include("../config/db.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>
<link rel="stylesheet" href="/pilates/assets/style.css">
</head>

<body>
<h1>Admin Dashboard</h1>
<p>Welcome, Administrator</p>
<a href="logout.php">Logout</a>
</body>
</html>
