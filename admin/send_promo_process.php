
<?php
include("../config/db.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../mailer/vendor/autoload.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sending Promotion</title>

    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            background-color: #f5f1e8; /* beige */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .result-container {
            background: #ffffff;
            width: 600px;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-top: 8px solid #0b4d2b; /* dark green */
        }

        h2 {
            color: #0b4d2b;
            margin-bottom: 20px;
            text-align: center;
        }

        .log {
            font-size: 14px;
            line-height: 1.6;
            max-height: 300px;
            overflow-y: auto;
            padding: 15px;
            background: #f9f7f1;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .success {
            color: #2e7d32;
        }

        .error {
            color: #c62828;
        }

        .final {
            margin-top: 20px;
            padding: 15px;
            background: #e8f5e9;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
            color: #0b4d2b;
        }

        .back-btn {
            display: block;
            margin-top: 25px;
            text-align: center;
        }

        .back-btn a {
            text-decoration: none;
            background: #0b4d2b;
            color: #f5f1e8;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: bold;
            transition: background 0.3s;
        }

        .back-btn a:hover {
            background: #083b21;
        }
    </style>
</head>

<body>

<div class="result-container">
    <h2>Sending Promotion Email</h2>

    <div class="log">
        <?php
        if (isset($_POST['sendPromo'])) {

    $promoTitle   = $_POST['promo_title'];
    $promoMessage = $_POST['promo_message'];

    $sql = "SELECT customer_Name, customer_Email FROM Customer";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }

    echo "<h3>Sending Promotion Email...</h3>";

    while ($row = mysqli_fetch_assoc($result)) {

        $customerName  = $row['customer_Name'];
        $customerEmail = $row['customer_Email'];

        $mail = new PHPMailer(true);

        try {
            // SMTP CONFIG (ICT600 LAB 8 STYLE)
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'azlshftn@gmail.com';      // 🔴 CHANGE
            $mail->Password   = 'ezgx socp ghgg nnpq';         // 🔴 CHANGE
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            // EMAIL DETAILS
            $mail->setFrom('YOUR_GMAIL@gmail.com', 'MyPilates');
            $mail->addAddress($customerEmail, $customerName);

            $mail->isHTML(true);
            $mail->Subject = $promoTitle;

            $mail->Body = "
                <p>Hi <b>$customerName</b>,</p>

                <p>$promoMessage</p>

                <br>
                <p>💚 <b>MyPilates Team</b></p>
            ";

            $mail->send();
            echo "Email sent to: $customerEmail<br>";

        } catch (Exception $e) {
            echo "Failed to send to $customerEmail<br>";
        }
    }

    echo "<br><b>All promotion emails sent successfully ✅</b>";
}
?>
 </div>
    <div class="back-btn">
        <a href="dashboard.php">← Back to Dashboard Page</a>
    </div>
</div>

</body>
</html>
