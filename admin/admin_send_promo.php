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
<title>Send Promotion</title>

<style>
    body {
        margin: 0;
        font-family: "Segoe UI", Arial, sans-serif;
        background-color: #f5f1e8;
        padding: 40px;
    }

    h1 {
        color: #0b4d2b;
        margin-bottom: 5px;
    }

    .subtitle {
        color: #555;
        margin-bottom: 25px;
    }

    .top-bar {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.back-dashboard {
    text-decoration: none;
    background: #0b4d2b;
    color: #f5f1e8;
    padding: 8px 14px;
    border-radius: 8px;
    font-weight: bold;
    font-size: 14px;
    transition: background 0.3s;
}

.back-dashboard:hover {
    background: #083b21;
}


    .card {
        background: #ffffff;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    }

    .card h3 {
        margin-top: 0;
        color: #0b4d2b;
        margin-bottom: 20px;
    }

    label {
        font-weight: 600;
        display: block;
        margin-bottom: 6px;
    }

    input, textarea {
        width: 100%;
        padding: 10px;
        border-radius: 6px;
        border: 1px solid #ccc;
        margin-bottom: 18px;
        font-size: 14px;
    }

    textarea {
        resize: none;
    }

    .btn {
        background: #0b4d2b;
        color: #f5f1e8;
        border: none;
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn:hover {
        background: #083b21;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    thead {
        background: #f3efe6;
    }

    th, td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    .success {
        color: #2e7d32;
        font-weight: 600;
    }

    .error {
        color: #c62828;
        font-weight: 600;
    }
</style>
</head>

<body>

    <div class="top-bar">
    <a href="dashboard.php" class="back-dashboard">
        ← Back to Dashboard
    </a>
</div>


<!-- PAGE TITLE -->
<h1>Send Promotion</h1>
<div class="subtitle">Send promotional emails to all registered customers</div>

<!-- ADD PROMOTION -->
<div class="card">
    <h3>➕ Add New Promotion</h3>

    <form method="post">
        <label>Promotion Title *</label>
        <input type="text" name="promo_title" placeholder="e.g. 20% Discount for Members" required>

        <label>Promotion Message *</label>
        <textarea name="promo_message" rows="5" placeholder="Enter promotion details here..." required></textarea>

        <button type="submit" name="sendPromo" class="btn">
            ➕ Send Promotion
        </button>
    </form>
</div>

<!-- EMAIL STATUS -->
<?php if (isset($_POST['sendPromo'])): ?>
<div class="card">
    <h3>📧 Email Status</h3>

    <table>
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>Email</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $promoTitle   = $_POST['promo_title'];
        $promoMessage = $_POST['promo_message'];

        $result = mysqli_query($conn,
            "SELECT customer_Name, customer_Email FROM Customer"
        );

        while ($row = mysqli_fetch_assoc($result)) {

            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'azlshftn@gmail.com';
                $mail->Password = 'ezgx socp ghgg nnpq';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('azlshftn@gmail.com', 'MyPilates');
                $mail->addAddress($row['customer_Email'], $row['customer_Name']);

                $mail->isHTML(true);
                $mail->Subject = $promoTitle;
                $mail->Body = "
                    Hi <b>{$row['customer_Name']}</b>,<br><br>
                    $promoMessage<br><br>
                    <b>💚 MyPilates Team</b>
                ";

                $mail->send();

                echo "<tr>
                        <td>{$row['customer_Name']}</td>
                        <td>{$row['customer_Email']}</td>
                        <td class='success'>Sent</td>
                      </tr>";

            } catch (Exception $e) {
                echo "<tr>
                        <td>{$row['customer_Name']}</td>
                        <td>{$row['customer_Email']}</td>
                        <td class='error'>Failed</td>
                      </tr>";
            }
        }
        ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

</body>
</html>
