<?php
include("../config/db.php");

$error = "";
$success = "";

if (!isset($_GET['token'])) {
    die("Invalid request.");
}

$token = $_GET['token'];

// Find token
$q = mysqli_query($conn,"
    SELECT customer_ID AS id, 'customer' AS role 
    FROM customer 
    WHERE reset_token='$token' AND reset_expires > NOW()
    UNION
    SELECT admin_ID AS id, 'admin' AS role
    FROM admin
    WHERE reset_token='$token' AND reset_expires > NOW()
    LIMIT 1
");

if (mysqli_num_rows($q) !== 1) {
    die("Invalid or expired token.");
}

$user = mysqli_fetch_assoc($q);

if (isset($_POST['update'])) {
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        if ($user['role'] === 'customer') {
            mysqli_query($conn,"
                UPDATE customer
                SET customer_Password='$hash',
                    reset_token=NULL,
                    reset_expires=NULL
                WHERE customer_ID={$user['id']}
            ");
        } else {
            mysqli_query($conn,"
                UPDATE admin
                SET admin_password='$password',
                    reset_token=NULL,
                    reset_expires=NULL
                WHERE admin_ID={$user['id']}
            ");
        }

        $success = "Password updated successfully.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Reset Password</title>
<link rel="stylesheet" href="/pilates/assets/style.css">
</head>
<body class="auth-page">

<div class="auth-container">
<div class="auth-form">
<h2>Reset Password</h2>

<?php if ($error): ?><p style="color:red"><?= $error ?></p><?php endif; ?>
<?php if ($success): ?><p style="color:green"><?= $success ?></p><?php endif; ?>

<form method="POST">
    <input type="password" name="password" placeholder="New Password" required>
    <input type="password" name="confirm" placeholder="Confirm Password" required>
    <button name="update">Update Password</button>
</form>

<a href="login.php">Back to Login</a>
</div>
</div>

</body>
</html>
