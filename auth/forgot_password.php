<?php
include("../config/db.php");

$msg = "";

if (isset($_POST['reset'])) {
    $email = $_POST['email'];

    $checkCustomer = mysqli_query($conn,"
        SELECT * FROM customer WHERE customer_Email='$email'
    ");

    $checkAdmin = mysqli_query($conn,"
        SELECT * FROM admin WHERE admin_Email='$email'
    ");

    if (mysqli_num_rows($checkCustomer) || mysqli_num_rows($checkAdmin)) {
        $msg = "Password reset link has been sent to your email (simulation).";
    } else {
        $msg = "Email not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Forgot Password</title>
<link rel="stylesheet" href="/pilates/assets/style.css">
</head>

<body class="auth-page">

<div class="auth-container">
    <div class="auth-form">
        <h2>Forgot Password</h2>
        <p>Enter your email to reset password</p>

        <?php if ($msg): ?>
            <p><?= $msg ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <button name="reset">Send Reset Link</button>
        </form>

        <p style="margin-top:20px;">
            <a href="login.php">Back to Login</a>
        </p>
    </div>

    <div class="auth-illustration">
        <img src="/pilates/assets/auth-forgot.png">
    </div>
</div>

</body>
</html>
