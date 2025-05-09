<?php

require "config.php";
session_start();

// Bật hiển thị lỗi
error_reporting(E_ALL);
ini_set("display_errors", 1);

// 🔥 Thêm header để bật CORS
header("Access-Control-Allow-Origin: http://localhost:1234");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Trả về JSON
header("Content-Type: application/json");

$expired_message = "";
$reset = null;

$token = $_GET["token"] ?? "";
$current_time = date("Y-m-d H:i:s");

// Kiểm tra token
if ($token) {
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ?");
    $stmt->execute([$token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($reset) {
        $expires = $reset["expires"];

        if ($current_time > $expires) {
            http_response_code(400);
            echo json_encode(["message" => "Liên kết đã hết hạn. Vui lòng yêu cầu một liên kết mới."]);
            exit;
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Mã xác thực không hợp lệ. Vui lòng kiểm tra lại."]);
        exit;
    }
}

// Xử lý yêu cầu POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Lấy dữ liệu JSON từ yêu cầu
    $input = json_decode(file_get_contents("php://input"), true);

    if (!$reset) {
        http_response_code(400);
        echo json_encode(["message" => "Mã xác thực không hợp lệ hoặc đã hết hạn."]);
        exit;
    }

    $new_password = $input["new_password"] ?? "";

    if (empty($new_password) || strlen($new_password) < 6) {
        http_response_code(400);
        echo json_encode(["message" => "Mật khẩu mới không hợp lệ. Vui lòng nhập mật khẩu có ít nhất 6 ký tự."]);
        exit;
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    if ($stmt->execute([$hashed_password, $reset["email"]])) {
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);

        echo json_encode(["message" => "Mật khẩu đã được cập nhật thành công."]);
        exit;
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Có lỗi xảy ra khi cập nhật mật khẩu. Vui lòng thử lại sau."]);
        exit;
    }
}

http_response_code(405); // Method Not Allowed
echo json_encode(["message" => "Phương thức không hợp lệ."]);
