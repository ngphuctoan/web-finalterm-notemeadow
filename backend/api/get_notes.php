<?php

require "config.php"; // Kết nối cơ sở dữ liệu
session_start();

// Định dạng phản hồi là JSON
header("Content-Type: application/json");
// Thêm CORS Headers
header("Access-Control-Allow-Origin: *"); // Cho phép tất cả nguồn (có thể thay * bằng localhost:3000)
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Kiểm tra phiên đăng nhập
// if (!isset($_SESSION["user_id"])) {
//     http_response_code(401); // Trả về mã lỗi 401
//     echo json_encode(["message" => "Chưa đăng nhập."]);
//     exit;
// }

// Lấy user_id từ session
$user_id = $_SESSION["user_id"] ?? 18;

try {
    // Truy vấn để lấy tất cả ghi chú của người dùng
    $stmt = $pdo->prepare("
        SELECT * FROM notes 
        WHERE user_id = ? 
        ORDER BY is_pinned DESC, modified_at DESC, created_at DESC
    ");
    $stmt->execute([$user_id]);

    // Lấy kết quả và xử lý đường dẫn ảnh
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($notes as &$note) {
        if (!empty($note["image"])) {
            $note["image"] = json_decode($note["image"], true); // Giải mã chuỗi JSON thành mảng
        }
    }

    // Trả về dữ liệu ghi chú dưới dạng JSON
    echo json_encode($notes);
} catch (PDOException $e) {
    http_response_code(500); // Trả về mã lỗi 500
    echo json_encode(["message" => "Lỗi khi lấy dữ liệu: " . htmlspecialchars($e->getMessage())]);
}
