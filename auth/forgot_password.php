<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("../config/db.php");

if (!$conn) {
    die("Database connection failed!");
}

require_once dirname(__DIR__) . '/mailer/vendor/phpmailer/phpmailer/src/Exception.php';
require_once dirname(__DIR__) . '/mailer/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once dirname(__DIR__) . '/mailer/vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$msg = "";

if (isset($_POST['reset'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // Check customer
    $userQ = mysqli_query($conn,"
        SELECT customer_ID AS id, 'customer' AS role 
        FROM customer WHERE customer_Email='$email'
        UNION
        SELECT admin_ID AS id, 'admin' AS role
        FROM admin WHERE admin_Email='$email'
        LIMIT 1
    ");

    if (mysqli_num_rows($userQ) === 1) {
        $user = mysqli_fetch_assoc($userQ);

        // Generate token
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        if ($user['role'] === 'customer') {
            mysqli_query($conn,"
                UPDATE customer 
                SET reset_token='$token', reset_expires='$expires'
                WHERE customer_Email='$email'
            ");
        } else {
            mysqli_query($conn,"
                UPDATE admin 
                SET reset_token='$token', reset_expires='$expires'
                WHERE admin_Email='$email'
            ");
        }

        // Reset link
        $resetLink = "http://localhost/pilates/auth/reset_password.php?token=$token";

        // Send email
        $mail = new PHPMailer(true);
        try {
            // SMTP Configuration - UPDATED SETTINGS
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'azlshftn@gmail.com';
            $mail->Password = 'ezgx socp ghgg nnpq';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
            // IMPORTANT: Add these timeout settings
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            $mail->Timeout = 30; // 30 seconds timeout
            
            // Debug (remove in production)
            // $mail->SMTPDebug = 2; // Uncomment to see connection details

            $mail->setFrom('azlshftn@gmail.com', 'MyPilates');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = "Reset Your MyPilates Password";
            $mail->Body = "
                <h2>Password Reset</h2>
                <p>Click the link below to reset your password:</p>
                <p><a href='$resetLink'>$resetLink</a></p>
                <p>This link expires in 1 hour.</p>
            ";

            if ($mail->send()) {
                $msg = "Password reset link sent to your email.";
            } else {
                $msg = "Email failed to send. Please try again later.";
            }
        } catch (Exception $e) {
            $msg = "Email service temporarily unavailable. Please try again in a few minutes.";
        }
    } else {
        $msg = "Email not found.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <style>
        body { font-family: Arial; max-width: 500px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; }
        input, button { width: 100%; padding: 10px; margin: 10px 0; }
    </style>
</head>
<body>
    <h2>Reset Password</h2>
    
    <?php if (!empty($msg)): ?>
        <div class="<?php echo (strpos($msg, 'sent') !== false) ? 'success' : 'error'; ?>">
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <label>Email:</label>
        <input type="email" name="email" required>
        <button type="submit" name="reset">Send Reset Link</button>
    </form>
    
    <p><a href="login.php">← Back to Login</a></p>
</body>
</html>