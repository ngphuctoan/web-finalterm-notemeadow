<?php

require "config.php"; // Kết nối cơ sở dữ liệu
session_start();

// 🔥 Bật CORS
header("Access-Control-Allow-Origin: http://localhost:1234");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Tell the browser it's okay
    header("Access-Control-Allow-Origin: http://localhost:1234");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    http_response_code(200);
    exit;
}

// Kiểm tra đăng nhập
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["message" => "Not logged in."]);
    exit;
}

// Kiểm tra phương thức
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed."]);
    exit;
}

try {
    $data = json_decode(file_get_contents("php://input"), true);
    $user_id = $_SESSION["user_id"];
    $current_password = $data["current_password"] ?? "";
    $new_password = $data["new_password"] ?? "";
    $confirm_password = $data["confirm_password"] ?? "";

    // Kiểm tra dữ liệu
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        http_response_code(400);
        echo json_encode(["message" => "All password fields are required."]);
        exit;
    }

    // Kiểm tra mật khẩu mới và xác nhận mật khẩu
    if ($new_password !== $confirm_password) {
        http_response_code(400);
        echo json_encode(["message" => "New password and confirm password do not match."]);
        exit;
    }

    // Kiểm tra độ dài mật khẩu mới
    if (strlen($new_password) < 6) {
        http_response_code(400);
        echo json_encode(["message" => "New password must be at least 6 characters long."]);
        exit;
    }

    // Lấy thông tin người dùng
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Kiểm tra mật khẩu hiện tại
    if (!password_verify($current_password, $user["password"])) {
        http_response_code(400);
        echo json_encode(["message" => "Current password is incorrect."]);
        exit;
    }

    // Cập nhật mật khẩu mới
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashed_password, $user_id]);

    echo json_encode(["message" => "Password has been updated successfully."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Error updating password: " . $e->getMessage()]);
}
