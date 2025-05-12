<?php

require "config.php"; // Kết nối cơ sở dữ liệu
session_start();

// 🔥 Thêm header để bật CORS
header("Access-Control-Allow-Origin: http://localhost:1234");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Trả về JSON
header("Content-Type: application/json");

// Kiểm tra phiên đăng nhập
if (!isset($_SESSION["user_id"])) {
    echo json_encode(["message" => "Not logged in."]);
    exit;
}

// Lấy user_id từ session
$user_id = $_SESSION["user_id"];

// Kiểm tra phương thức yêu cầu
if ($_SERVER["REQUEST_METHOD"] === "DELETE") {
    // Cập nhật hình ảnh mặc định
    $defaultImage = ""; // Đường dẫn hình ảnh mặc định
    $stmt = $pdo->prepare("UPDATE users SET image = ? WHERE id = ?");

    if ($stmt->execute([$defaultImage, $user_id])) {
        echo json_encode(["message" => "Current avatar has been removed."]);
    } else {
        echo json_encode(["message" => "Unable to update user information."]);
    }
} else {
    echo json_encode(["message" => "Invalid method."]);
}
