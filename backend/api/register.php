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
<html>
<head>
    <title>Email Confirmation</title>
</head>
<body style="margin-top:20px;">
    <table class="body-wrap" style="font-family: "Helvetica Neue",Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; background-color: #f6f6f6; margin: 0;" bgcolor="#f6f6f6">
        <tbody>
            <tr>
                <td valign="top"></td>
                <td class="container" width="600" valign="top">
                    <div class="content" style="padding: 20px;">
                        <table class="main" width="100%" cellpadding="0" cellspacing="0" style="border-radius: 3px; background-color: #fff; margin: 0; border: 1px solid #e9e9e9;" bgcolor="#fff">
                            <tbody>
                                <tr>
                                    <td class="" style="font-size: 16px; vertical-align: top; color: #fff; font-weight: 500; text-align: center; background-color: #38414a; padding: 20px;" align="center" bgcolor="#71b6f9" valign="top">
                                        <a href="#" style="font-size:32px;color:#fff;text-decoration: none;">Hi there!</a> <br>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="content-wrap" style="padding: 20px;" valign="top">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tbody>
                                                <tr>
                                                    <td class="content-block" style="padding: 0 0 20px;" valign="top">
                                                       Thank you for creating an Note account. To continue setting up your workspace, please verify your email by clicking the link below:
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="content-block" style="text-align: center;" valign="top">
                                                        <a href="$activation_link" class="btn-primary" style="font-size: 14px; color: #FFF; text-decoration: none; line-height: 2em; font-weight: bold; border-radius: 5px; background-color: #D10024; padding: 8px 16px; display: inline-block;">Verify my email address</a>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="content-block" style="padding: 0 0 20px;" valign="top">
                                                        This link will verify your email address, and then you’ll officially be a part of the Note Website community.
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="content-block" style="padding: 0 0 20px;" valign="top">
                                                        See you there!
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="content-block" style="padding: 0 0 20px;" valign="top">
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
                <td valign="top"></td>
            </tr>
        </tbody>
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
