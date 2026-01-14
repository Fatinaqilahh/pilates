<?php
session_start();
include("../config/db.php");

$error = "";

if (isset($_POST['login'])) {

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    /* =========================
       1️⃣ CHECK ADMIN ACCOUNT
       ========================= */
    $adminQ = mysqli_query($conn, "
        SELECT * FROM admin 
        WHERE admin_Email = '$email'
        LIMIT 1
    ");

    if (mysqli_num_rows($adminQ) === 1) {
        $admin = mysqli_fetch_assoc($adminQ);

        // ⚠️ If admin password is PLAIN TEXT
        if ($password === $admin['admin_password']) {
            $_SESSION['admin_id'] = $admin['admin_ID'];
            header("Location: ../admin/dashboard.php");
            exit;
        } else {
            $error = "Incorrect email or password";
        }

    } else {

        /* =========================
           2️⃣ CHECK CUSTOMER
           ========================= */
        $custQ = mysqli_query($conn, "
            SELECT * FROM customer 
            WHERE customer_Email = '$email'
            LIMIT 1
        ");

        if (mysqli_num_rows($custQ) === 1) {
            $user = mysqli_fetch_assoc($custQ);

            if (password_verify($password, $user['customer_Password'])) {
                $_SESSION['customer_id'] = $user['customer_ID'];
                header("Location: ../member/dashboard.php");
                exit;
            } else {
                $error = "Incorrect email or password";
            }

        } else {
            $error = "Account not found";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login | MyPilates</title>
<link rel="stylesheet" href="/pilates/assets/style.css">

<style>
    .required-label {
        display: block;
        margin-bottom: 5px;
        color: #D2B48C; /* Beige color - ONLY for Email Address and Password labels */
    }
    .required-asterisk {
        color: #ff0000;
    }
</style>

<script>
function togglePassword() {
    const input = document.getElementById("passwordInput");
    input.type = input.type === "password" ? "text" : "password";
}
</script>
</head>

<body class="auth-page">

<a href="/pilates/public/index.php" class="auth-back">
    ← Back to Home
</a>

<div class="auth-container">

    <!-- LOGIN FORM -->
    <div class="auth-form">
        <h2>Welcome Back</h2>
        <p>Login to your MyPilates account</p>

        <?php if ($error): ?>
            <div class="auth-error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            
            <label class="required-label">Email Address <span class="required-asterisk">*</span></label>
            <input 
                type="email" 
                name="email" 
                placeholder="" 
                required
            >

            <label class="required-label">Password <span class="required-asterisk">*</span></label>
            <div class="password-wrapper">
                <input 
                    type="password" 
                    name="password" 
                    id="passwordInput"
                    placeholder="" 
                    required
                >
                <span class="toggle-password" onclick="togglePassword()">👁️</span>
            </div>

            <div class="forgot-link">
                <a href="forgot_password.php">Forgot password?</a>
            </div>

            <button type="submit" name="login">Login</button>
        </form>

        <p class="auth-footer">
            New member? <a href="register.php">Create an account</a>
        </p>
    </div>

    <!-- IMAGE -->
    <div class="auth-illustration">
        <img src="/pilates/assets/auth-login.jpg" alt="MyPilates Login">
    </div>

</div>

</body>
</html>