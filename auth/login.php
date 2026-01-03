<?php
session_start();
include("../config/db.php");

$error = "";
$role = $_POST['role'] ?? 'customer';

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if ($role === 'admin') {

        $q = mysqli_query($conn,"
            SELECT * FROM admin 
            WHERE admin_Email='$email' 
            LIMIT 1
        ");

        if (mysqli_num_rows($q) === 1) {
            $admin = mysqli_fetch_assoc($q);

            if (password_verify($password, $admin['admin_Password'])) {
                $_SESSION['admin_id'] = $admin['admin_ID'];
                header("Location: ../admin/dashboard.php");
                exit;
            } else {
                $error = "Incorrect admin password";
            }
        } else {
            $error = "Admin account not found";
        }

    } else {

        $q = mysqli_query($conn,"
            SELECT * FROM customer 
            WHERE customer_Email='$email' 
            LIMIT 1
        ");

        if (mysqli_num_rows($q) === 1) {
            $user = mysqli_fetch_assoc($q);

            if (password_verify($password, $user['customer_Password'])) {
                $_SESSION['customer_id'] = $user['customer_ID'];
                header("Location: ../member/dashboard.php");
                exit;
            } else {
                $error = "Incorrect password";
            }
        } else {
            $error = "Customer account not found";
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body class="auth-page">

    <a href="/pilates/public/index.php" class="auth-back">
    ← Back to Home
</a>


<div class="auth-container">

    <!-- LEFT -->
    <div class="auth-form">
        <h2>Welcome Back</h2>
        <p>Select your role and login</p>

        <?php if ($error): ?>
            <div class="auth-error"><?= $error ?></div>
        <?php endif; ?>

        <div class="role-switch">
    <button type="button"
        class="role-btn <?= $role === 'customer' ? 'active' : '' ?>"
        data-role="customer">
        👤 Customer
    </button>

    <button type="button"
        class="role-btn <?= $role === 'admin' ? 'active' : '' ?>"
        data-role="admin">
        🛠 Admin
    </button>
</div>

<input type="hidden" name="role" value="<?= $role ?>">



        <form method="POST">
            <input type="hidden" name="role" value="<?= $role ?>">

            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>

            <div class="forgot-link">
                <a href="forgot_password.php">Forgot password?</a>
            </div>

            <button type="submit" name="login">Login</button>
        </form>

        <p class="auth-footer">
            Customer only? <a href="register.php">Sign up</a>
        </p>
    </div>

    <!-- RIGHT -->
    <div class="auth-illustration">
        <img src="/pilates/assets/auth-login.jpg" alt="Login">
    </div>

</div>

<script>
const roleButtons = document.querySelectorAll('.role-btn');
const roleInput = document.querySelector('input[name="role"]');

roleButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        roleButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        roleInput.value = btn.dataset.role;
    });
});
</script>


</body>
</html>
