
<?php
session_start();
include("../config/db.php");

if (isset($_POST['register'])) {

    $name     = mysqli_real_escape_string($conn, $_POST['name']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $phone    = mysqli_real_escape_string($conn, $_POST['phone']);
    $address    = mysqli_real_escape_string($conn, $_POST['address']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    // 🔒 Password confirmation check
    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

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
                (customer_Name, customer_Email, customer_Phone,customer_address,customer_Password, customer_status)
                VALUES ('$name','$email','$phone','$address','$hashed_password','ACTIVE')
            ");

            $customer_id = mysqli_insert_id($conn);

            // 🔎 Find FREE plan
            $freePlanQuery = mysqli_query($conn, "
                SELECT plan_ID FROM membershipplan WHERE plan_Price = 0.00 LIMIT 1
            ");

            if (mysqli_num_rows($freePlanQuery) > 0) {
                $freePlan = mysqli_fetch_assoc($freePlanQuery);
                $free_plan_id = $freePlan['plan_ID'];
            } else {
                $free_plan_id = 17; // fallback
            }

            // Auto FREE membership
            mysqli_query($conn, "
                INSERT INTO customermembership 
                (customer_ID, plan_ID, membership_Status, start_Date)
                VALUES ($customer_id, $free_plan_id, 'ACTIVE', CURDATE())
            ");

            $_SESSION['customer_id'] = $customer_id;
            header("Location: ../member/dashboard.php");
            exit;
        }
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

            <label>Full Name <span style="color:red">*</span></label>
            <input type="text" name="name" required>

            <label>Email <span style="color:red">*</span></label>
            <input type="email" name="email" required>

            <label>Phone Number <span style="color:red">*</span></label>
            <input type="text" name="phone" required>

            <label>Address <span style="color:red">*</span></label>
            <input type="text" name="address" required>

           <label>Password <span style="color:red">*</span></label>
<div class="password-wrapper">
    <input 
        type="password" 
        name="password" 
        id="password" 
        required
    >
    <span class="toggle-password" onclick="togglePassword('password')">👁️</span>
</div>

<div id="strengthText" class="password-strength"></div>


            <label>Confirm Password <span style="color:red">*</span></label>
<div class="password-wrapper">
    <input 
        type="password" 
        name="confirm_password" 
        id="confirm_password" 
        required
    >
    <span class="toggle-password" onclick="togglePassword('confirm_password')">👁️</span>
</div>


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