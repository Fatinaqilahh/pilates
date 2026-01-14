<?php
session_start();
include("../config/db.php");

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id = (int) $_SESSION['customer_id'];

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form data
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    
    // Basic validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    
    // Check if email already exists (excluding current user)
    $checkEmailQuery = "SELECT customer_ID FROM customer WHERE customer_Email = '$email' AND customer_ID != $id";
    $checkEmailResult = mysqli_query($conn, $checkEmailQuery);
    
    if (mysqli_num_rows($checkEmailResult) > 0) {
        $errors[] = "This email is already registered.";
    }
    
    // If there are no errors, update the database
    if (empty($errors)) {
        // Build the update query
        $updateQuery = "UPDATE customer SET 
                       customer_Name = '$name',
                       customer_Email = '$email',
                       customer_Phone = '$phone'
                       WHERE customer_ID = $id";
        
        if (mysqli_query($conn, $updateQuery)) {
            // Success - redirect back to dashboard with success message
            $_SESSION['success_message'] = "Profile updated successfully!";
            header("Location: dashboard.php");
            exit;
        } else {
            // Database error
            $_SESSION['error_message'] = "Error updating profile: " . mysqli_error($conn);
            header("Location: dashboard.php");
            exit;
        }
    } else {
        // Validation errors - redirect back with error messages
        $_SESSION['error_message'] = implode("<br>", $errors);
        header("Location: dashboard.php");
        exit;
    }
} else {
    // If accessed directly without POST, redirect to dashboard
    header("Location: dashboard.php");
    exit;
}
?>