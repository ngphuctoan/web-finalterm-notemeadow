<?php

require __DIR__ . "/../vendor/autoload.php"; // Đảm bảo đường dẫn đúng

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmail($to, $subject, $body)
{
    $mail = new PHPMailer(true);

    try {
        // Cấu hình SMTP
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = "quocdatforwork@gmail.com"; // Email của bạn
        $mail->Password = "ddabeyoldairwpsl"; // Mật khẩu ứng dụng
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Người gửi & người nhận
        $mail->setFrom("quocdatforwork@gmail.com", "Ghi chú");
        $mail->addAddress($to);

        // Nội dung email
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        // Set content-type header for UTF-8
        $mail->CharSet = "UTF-8";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
