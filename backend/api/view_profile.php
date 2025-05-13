<?php

require_once "config.php"; // Kết nối cơ sở dữ liệu
session_start();

set_cors_header();
check_login();

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
