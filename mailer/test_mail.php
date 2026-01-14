<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'azlshftn@gmail.com';
    $mail->Password = 'ezgx socp ghgg nnpq';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('YOUR_GMAIL@gmail.com', 'Test Mail');
    $mail->addAddress('YOUR_GMAIL@gmail.com');

    $mail->isHTML(true);
    $mail->Subject = 'ICT600 Test Email';
    $mail->Body = 'Email is working ✅';

    $mail->send();
    echo 'SUCCESS';
} catch (Exception $e) {
    echo $mail->ErrorInfo;
}
