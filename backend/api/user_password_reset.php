<?php

require "config.php";
require "send_email.php"; // Nhúng tệp gửi email
session_start(); // Khởi động session nếu cần

// 🔥 Thêm header để bật CORS
header("Access-Control-Allow-Origin: http://localhost:1234");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Trả về JSON
header("Content-Type: application/json");

// Kiểm tra phương thức
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed."]);
    exit;
}

try {
    $data = json_decode(file_get_contents("php://input"), true);
    $email = $data["email"] ?? "";

    // Kiểm tra email
    if (empty($email)) {
        http_response_code(400);
        echo json_encode(["message" => "Email is required."]);
        exit;
    }

    // Kiểm tra email hợp lệ
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid email format."]);
        exit;
    }

    // Kiểm tra email tồn tại
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["message" => "Email not found."]);
        exit;
    }

    // Tạo token reset password
    $token = bin2hex(random_bytes(32));
    $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

    // Lưu token vào database
    $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE email = ?");
    if ($stmt->execute([$token, $expires, $email])) {
        // Gửi email reset password
        $reset_link = "http://yourdomain.com/reset-password?token=" . $token;
        $to = $email;
        $subject = "Password Reset Request";
        $message = "Hello,\n\n";
        $message .= "You have requested to reset your password. Click the link below to reset your password:\n\n";
        $message .= $reset_link . "\n\n";
        $message .= "This link will expire in 1 hour.\n\n";
        $message .= "If you did not request this, please ignore this email.\n\n";
        $message .= "Best regards,\nYour App Team";

        $headers = "From: noreply@yourdomain.com\r\n";
        $headers .= "Reply-To: noreply@yourdomain.com\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        if (mail($to, $subject, $message, $headers)) {
            echo json_encode(["message" => "A password reset link has been sent to your email."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error sending reset email."]);
        }
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error saving reset token."]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Error processing request: " . $e->getMessage()]);
}
