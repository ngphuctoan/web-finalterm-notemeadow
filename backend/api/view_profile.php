<?php

require "config.php"; // Kết nối cơ sở dữ liệu
session_start();


// 🔥 Thêm header để bật CORS
header("Access-Control-Allow-Origin: http://localhost:1234");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Trả về JSON
header("Content-Type: application/json");

// Kiểm tra phiên đăng nhập
if (!isset($_SESSION["user_id"])) {
    http_response_code(401); // Trả về mã lỗi 401
    echo json_encode(["message" => "Chưa đăng nhập."]);
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
    http_response_code(500); // Trả về mã lỗi 500
    echo json_encode(["message" => "Lỗi khi lấy dữ liệu: " . htmlspecialchars($e->getMessage())]);
}
