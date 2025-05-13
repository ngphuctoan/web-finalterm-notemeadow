<?php

require "config.php";
require "send_email.php"; // Include the email sending function

// 🔥 Thêm header để bật CORS
header("Access-Control-Allow-Origin: http://localhost:1234");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Trả về JSON
header("Content-Type: application/json");

function sendActivationEmail($to, $user_name, $activation_token)
{
    $subject = "Verify your Note account";
    $activation_link = "http://" . $_SERVER["HTTP_HOST"] . "/api/activate_account.php?token=" . $activation_token;

    // Using heredoc for better readability
    $body = <<<EOD
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Email Confirmation</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600&display=swap" rel="stylesheet" />
</head>
<body style="margin: 0; padding: 0; background-color: #f9fafb; font-family: 'Plus Jakarta Sans', sans-serif; color: #111827;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f9fafb; padding: 40px 0;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden;">
          <!-- Header -->
          <tr>
            <td align="center" style="background-color: #d89614; padding: 24px;">
              <h1 style="color: white; font-size: 28px; margin: 0;">Confirm Your Email</h1>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding: 30px;">
              <p style="font-size: 16px; line-height: 1.6; margin-bottom: 24px;">
                Thank you for signing up for <strong>notemeadow</strong>! You're just one step away from accessing your notes.
              </p>

              <p style="font-size: 16px; line-height: 1.6; margin-bottom: 24px;">
                Please confirm your email address by clicking the button below:
              </p>

              <div style="text-align: center; margin: 32px 0;">
                <a href="$activation_link" style="background-color: #d89614; color: #ffffff; padding: 12px 24px; border-radius: 5px; text-decoration: none; font-weight: 600;">
                  Verify My Email
                </a>
              </div>

              <p style="font-size: 14px; color: #6b7280; line-height: 1.6; margin-bottom: 20px;">
                If the button doesn't work, copy and paste this link into your browser:
                <br>
                <a href="$activation_link" style="color: #00BFFF; word-break: break-all;">$activation_link</a>
              </p>

              <p style="font-size: 14px; color: #6b7280;">
                Once verified, you'll officially become part of the notemeadow community.
              </p>

              <p style="margin-top: 32px; font-size: 14px; color: #6b7280;">
                See you there!<br>
                <strong>The notemeadow Team</strong>
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td align="center" style="padding: 20px; font-size: 12px; color: #9ca3af;">
              &copy; 2025 notemeadow. All rights reserved.
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
EOD;

    return sendEmail($to, $subject, $body);
}

$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $data["email"] ?? "";
    $display_name = $data["display_name"] ?? "";
    $password = $data["password"] ?? "";
    $password_confirmation = $data["password_confirmation"] ?? "";

    // Kiểm tra xem email có trống không
    if (empty($email)) {
        echo json_encode(["message" => "Email không được phép để trống."]);
        exit;
    } elseif (empty($display_name)) {
        echo json_encode(["message" => "Tên hiển thị không được phép để trống."]);
        exit;
    }

    // Kiểm tra email có hợp lệ không (có thể sử dụng filter_var để kiểm tra email)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["message" => "Email không hợp lệ."]);
        exit;
    }

    // Kiểm tra mật khẩu có trống không
    if (empty($password)) {
        echo json_encode(["message" => "Mật khẩu không được để trống."]);
        exit;
    }

    // Kiểm tra mật khẩu có ít nhất 8 ký tự không
    if (strlen($password) < 8) {
        echo json_encode(["message" => "Mật khẩu phải có ít nhất 8 ký tự."]);
        exit;
    }

    // Kiểm tra mật khẩu và mật khẩu xác nhận có khớp không
    if ($password !== $password_confirmation) {
        echo json_encode(["message" => "Mật khẩu và xác nhận mật khẩu không khớp."]);
        exit;
    }

    try {
        // Kiểm tra xem email đã tồn tại chưa
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(["message" => "Email đã được sử dụng."]);
            exit;
        }

        // Mã hóa mật khẩu
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $activation_token = bin2hex(random_bytes(16));

        // Chèn thông tin người dùng vào cơ sở dữ liệu
        $stmt = $pdo->prepare("INSERT INTO users (email, display_name, password, activation_token) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$email, $display_name, $hashed_password, $activation_token])) {
            // Gửi email kích hoạt
            if (!sendActivationEmail($email, $display_name, $activation_token)) {
                echo json_encode(["message" => "Không thể gửi email kích hoạt."]);
                exit;
            }

            // Tự động đăng nhập
            session_start();
            $_SESSION["user_id"] = $pdo->lastInsertId();
            $_SESSION["user_email"] = $email; // Lưu email vào session

            echo json_encode(["message" => "Đăng ký thành công, vui lòng kiểm tra email để kích hoạt."]);
        } else {
            echo json_encode(["message" => "Có lỗi khi đăng ký tài khoản."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["message" => "Lỗi cơ sở dữ liệu: " . $e->getMessage()]);
    } catch (Exception $e) {
        echo json_encode(["message" => "Lỗi hệ thống: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["message" => "Yêu cầu không hợp lệ."]);
}
