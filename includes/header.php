<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MyPilates</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/pilates/assets/style.css">
</head>
<body>

<header class="site-header">
    <div class="logo">
        <a href="/pilates/public/">MyPilates</a>
    </div>

    <nav class="nav-links">
        <?php if(!isset($_SESSION['customer_id']) && !isset($_SESSION['admin_id'])): ?>
            <a href="/pilates/public/#about">About</a>
            <a href="/pilates/public/#classes">Classes</a>
            <a href="/pilates/public/plans.php">Memberships</a>
            <a href="/pilates/auth/login.php">Login</a>
            <a href="/pilates/auth/register.php" class="btn">Sign Up</a>

        <?php elseif(isset($_SESSION['customer_id'])): ?>
            <a href="/pilates/member/dashboard.php">Dashboard</a>
            <a href="/pilates/member/book.php">Book</a>
            <a href="/pilates/member/upgrade.php">Membership</a>
            <a href="/pilates/member/history.php">Payments</a>
            <a href="/pilates/auth/logout.php" class="btn">Logout</a>

        <?php elseif(isset($_SESSION['admin_id'])): ?>
            <a href="/pilates/admin/dashboard.php">Admin</a>
            <a href="/pilates/auth/logout.php" class="btn">Logout</a>
        <?php endif; ?>
    </nav>
</header>

