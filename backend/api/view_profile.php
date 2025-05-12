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
    http_response_code(401); // Return 401 error code
    echo json_encode(["message" => "Not logged in."]);
    exit;
}

// Lấy user_id từ session
$user_id = $_SESSION["user_id"];

try {
    // Truy vấn để lấy tất cả ghi chú của người dùng
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? ");
    $stmt->execute([$user_id]);

    // Lấy kết quả và xử lý đường dẫn ảnh
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    // Trả về dữ liệu ghi chú dưới dạng JSON
    echo json_encode($profile);
} catch (PDOException $e) {
    http_response_code(500); // Return 500 error code
    echo json_encode(["message" => "Error retrieving data: " . htmlspecialchars($e->getMessage())]);
}
