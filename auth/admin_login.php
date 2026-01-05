<?php
include("../config/db.php");
include("../includes/header.php");

if(isset($_POST['admin_login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $q = mysqli_query($conn,"
        SELECT * FROM admin WHERE admin_Email='$email'
    ");

    if(mysqli_num_rows($q)==1){
        $admin = mysqli_fetch_assoc($q);

        if($password == $admin['admin_password']){
            $_SESSION['admin_id'] = $admin['admin_ID'];
            header("Location: ../admin/dashboard.php");
            exit;
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "Admin not found";
    }
}
?>

<section class="auth-page">

<div class="auth-container">

    <div class="auth-form">
        <h2>Admin Login</h2>
        <p>Authorized access only</p>

        <?php if(isset($error)): ?>
            <p style="color:red"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="email" name="email" placeholder="Admin Email" req
