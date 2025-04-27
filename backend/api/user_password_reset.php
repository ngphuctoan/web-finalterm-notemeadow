<?php
require 'config.php';
require 'send_email.php'; // Nhúng tệp gửi email
session_start(); // Khởi động session nếu cần

// 🔥 Thêm header để bật CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Trả về JSON
header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu JSON từ yêu cầu
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (isset($data['email'])) {
        $email = $data['email'];

        // Kiểm tra xem email có tồn tại trong cơ sở dữ liệu không
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Tạo mã xác thực
            $token = bin2hex(random_bytes(50)); // Tạo mã xác thực ngẫu nhiên
            $expires = date("Y-m-d H:i:s", strtotime('+15 minutes')); // Thời gian hết hạn là 30 giây

            // Xóa mã xác thực cũ nếu có
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$email]);

            // Lưu mã xác thực mới vào cơ sở dữ liệu
            $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires) VALUES (?, ?, ?)");
            if (!$stmt->execute([$email, $token, $expires])) {
                echo json_encode(['message' => 'Có lỗi xảy ra khi lưu mã xác thực.']);
                exit;
            }

            // Tạo liên kết đặt lại mật khẩu
            $resetLink = "http://localhost/note_management/api/resetpass.php?token=" . $token;

            // Gửi email với liên kết đặt lại mật khẩu
            $subject = "Reset your Note password - $email";
            $message = <<<EOD
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Reset your Note password</title>
                </head>
                <body style='margin-top:20px;'>
                    <table class='body-wrap' style='font-family: "Helvetica Neue",Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; background-color: #f6f6f6; margin: 0;' bgcolor='#f6f6f6'>
                        <tbody>
                            <tr>
                                <td valign='top'></td>
                                <td class='container' width='600' valign='top'>
                                    <div class='content' style='padding: 20px;'>
                                        <table class='main' width='100%' cellpadding='0' cellspacing='0' style='border-radius: 3px; background-color: #fff; margin: 0; border: 1px solid #e9e9e9;' bgcolor='#fff'>
                                            <tbody>
                                                <tr>
                                                    <td class='' style='font-size: 16px; vertical-align: top; color: #fff; font-weight: 500; text-align: center; background-color: #38414a; padding: 20px;' align='center' bgcolor='#71b6f9' valign='top'>
                                                        <a href='#' style='font-size:32px;color:#fff;text-decoration: none;'>Hi there!</a> <br>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class='content-wrap' style='padding: 20px;' valign='top'>
                                                        <table width='100%' cellpadding='0' cellspacing='0'>
                                                            <tbody>
                                                                <tr>
                                                                    <td class='content-block' style='padding: 0 0 20px;' valign='top'>
                                                                        Someone (hopefully you) has requested a password reset for your Note account. Follow the link below to set a new password:
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class='content-block' style='padding: 0 0 20px;' valign='top'>
                                                                        <a href=" $resetLink"> $resetLink </a> 
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td class='content-block' style='padding: 0 0 20px;' valign='top'>
                                                                       If you don't wish to reset your password, disregard this email and no action will be taken.
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class='content-block' style='padding: 0 0 20px;' valign='top'>
                                                                        Best regards, the Note Website team.
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                                <td valign='top'></td>
                            </tr>
                        </tbody>
                    </table>
                </body>
                </html>
                EOD;

            if (sendEmail($email, $subject, $message)) {
                echo json_encode(['message' => 'Một liên kết đặt lại mật khẩu đã được gửi đến email của bạn.']);
            } else {
                echo json_encode(['message' => 'Có lỗi xảy ra khi gửi email.']);
            }
        } else {
            echo json_encode(['message' => 'Email không tồn tại.']);
        }
    } else {
        echo json_encode(['message' => 'Vui lòng cung cấp email.']);
    }
} else {
    echo json_encode(['message' => 'Yêu cầu không hợp lệ.']);
}
?>