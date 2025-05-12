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


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Lấy dữ liệu JSON từ yêu cầu
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data["email"])) {
        $email = $data["email"];

        // Kiểm tra xem email có tồn tại trong cơ sở dữ liệu không
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Tạo mã xác thực
            $token = bin2hex(random_bytes(50)); // Tạo mã xác thực ngẫu nhiên
            $expires = date("Y-m-d H:i:s", strtotime("+15 minutes")); // Thời gian hết hạn là 30 giây

            // Xóa mã xác thực cũ nếu có
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$email]);

            // Lưu mã xác thực mới vào cơ sở dữ liệu
            $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires) VALUES (?, ?, ?)");
            if (!$stmt->execute([$email, $token, $expires])) {
                echo json_encode(["message" => "Có lỗi xảy ra khi lưu mã xác thực."]);
                exit;
            }

            $protocol = isset($_SERVER["HTTP_HOST"]) && $_SERVER["HTTP_HOST"] === "on" ? "https" : "http";

            // Tạo liên kết đặt lại mật khẩu
            $resetLink = "$protocol://$_SERVER[HTTP_HOST]/api/resetpass.php?token=" . $token;

            // Gửi email với liên kết đặt lại mật khẩu
            $subject = "Reset your Note password - $email";
            $message = <<<EOD
                <!DOCTYPE html>
                <html lang="en">
                <head>
                <meta charset="UTF-8" />
                <title>Reset your Note password</title>
                <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600&display=swap" rel="stylesheet" />
                </head>
                <body style="margin: 0; padding: 0; background-color: #f9fafb; font-family: 'Plus Jakarta Sans', sans-serif; color: #111827;">
                <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f9fafb; padding: 40px 0;">
                    <tr>
                    <td align="center">
                        <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden;">
                        <!-- Header -->
                        <tr>
                            <td align="center" style="background-color: #d89614; padding: 24px;">
                            <h1 style="color: white; font-size: 28px; margin: 0;">Reset Your Password</h1>
                            </td>
                        </tr>

                        <!-- Body -->
                        <tr>
                            <td style="padding: 30px;">
                            <p style="font-size: 16px; line-height: 1.6; margin-bottom: 24px;">
                                Hi there,<br><br>
                                We received a request to reset your password for your Note account. If this was you, click the button below to set a new password.
                            </p>

                            <div style="text-align: center; margin: 32px 0;">
                                <a href="$resetLink" style="background-color: #d89614; color: #ffffff; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600;">
                                Reset Password
                                </a>
                            </div>

                            <p style="font-size: 14px; color: #6b7280; line-height: 1.6; margin-bottom: 20px;">
                                Or copy and paste this link into your browser:
                                <br>
                                <a href="$resetLink" style="color: #d89614; word-break: break-all;">$resetLink</a>
                            </p>

                            <p style="font-size: 14px; color: #6b7280;">
                                If you didn’t request a password reset, you can safely ignore this email — no changes will be made.
                            </p>

                            <p style="margin-top: 32px; font-size: 14px; color: #6b7280;">
                                Best regards,<br>
                                <strong>NoteMeadow Team</strong>
                            </p>
                            </td>
                        </tr>

                        <!-- Footer -->
                        <tr>
                            <td align="center" style="padding: 20px; font-size: 12px; color: #9ca3af;">
                            &copy; 2025 NoteMeadow. All rights reserved.
                            </td>
                        </tr>
                        </table>
                    </td>
                    </tr>
                </table>
                </body>
                </html>
                EOD;

            if (sendEmail($email, $subject, $message)) {
                echo json_encode(["message" => "Một liên kết đặt lại mật khẩu đã được gửi đến email của bạn."]);
            } else {
                echo json_encode(["message" => "Có lỗi xảy ra khi gửi email."]);
            }
        } else {
            echo json_encode(["message" => "Email không tồn tại."]);
        }
    } else {
        echo json_encode(["message" => "Vui lòng cung cấp email."]);
    }
} else {
    echo json_encode(["message" => "Yêu cầu không hợp lệ."]);
}
