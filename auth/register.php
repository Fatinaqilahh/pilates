<?php
session_start();
include("../config/db.php");

if (isset($_POST['register'])) {

    $name     = mysqli_real_escape_string($conn, $_POST['name']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // 🔍 Check duplicate email
    $check = mysqli_query($conn, "
        SELECT customer_ID FROM customer WHERE customer_Email='$email'
    ");

    if (mysqli_num_rows($check) > 0) {
        $error = "Email already registered.";
    } else {

        // Insert customer
        mysqli_query($conn, "
            INSERT INTO customer 
            (customer_Name, customer_Email, customer_Password, customer_status)
            VALUES ('$name','$email','$password','ACTIVE')
        ");

        $customer_id = mysqli_insert_id($conn);

        // Dynamically find Free plan ID (plan with price 0.00)
        $freePlanQuery = mysqli_query($conn, "
            SELECT plan_ID FROM membershipplan WHERE plan_Price = 0.00 LIMIT 1
        ");
        
        if (mysqli_num_rows($freePlanQuery) > 0) {
            $freePlan = mysqli_fetch_assoc($freePlanQuery);
            $free_plan_id = $freePlan['plan_ID'];
            
            // Auto FREE membership
            mysqli_query($conn, "
                INSERT INTO customermembership 
                (customer_ID, plan_ID, membership_Status, start_Date)
                VALUES ($customer_id, $free_plan_id, 'ACTIVE', CURDATE())
            ");
        } else {
            // Fallback to plan_ID = 17 if no free plan found
            mysqli_query($conn, "
                INSERT INTO customermembership 
                (customer_ID, plan_ID, membership_Status, start_Date)
                VALUES ($customer_id, 17, 'ACTIVE', CURDATE())
            ");
        }

        $_SESSION['customer_id'] = $customer_id;
        header("Location: ../member/dashboard.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sign Up | MyPilates</title>
<link rel="stylesheet" href="/pilates/assets/style.css">
</head>

<body class="auth-page">
    <a href="/pilates/public/index.php" class="auth-back">
    ← Back to Home
</a>

<div class="auth-container">

    <div class="auth-form">
        <h2>Create Account</h2>
        <p>Start your Pilates journey</p>

        <?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>

        <form method="POST">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>

            <input type="password" 
                   name="password" 
                   id="password" 
                   placeholder="Password" 
                   required>

            <div id="strengthText" class="password-strength"></div>

            <button name="register">Sign Up</button>
        </form>

        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>

    <div class="auth-illustration">
        <img src="/pilates/assets/auth/auth-illustration.jpg" alt="Pilates Illustration">
    </div>

</div>

<!-- ✅ PASSWORD STRENGTH SCRIPT -->
<script>
const password = document.getElementById("password");
const strengthText = document.getElementById("strengthText");

password.addEventListener("input", () => {
    const value = password.value;

    if (value.length < 6) {
        strengthText.textContent = "Weak password";
        strengthText.className = "password-strength strength-weak";
    } 
    else if (value.match(/[A-Z]/) && value.match(/[0-9]/)) {
        strengthText.textContent = "Strong password";
        strengthText.className = "password-strength strength-strong";
    } 
    else {
        strengthText.textContent = "Medium password";
        strengthText.className = "password-strength strength-medium";
    }
});
</script>

</body>
</html>