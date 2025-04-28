<?php

require "config.php"; // Kết nối cơ sở dữ liệu
session_start();

// Định dạng phản hồi là JSON
header("Content-Type: application/json");
// Thêm CORS Headers
header("Access-Control-Allow-Origin: *"); // Cho phép tất cả nguồn (có thể thay * bằng localhost:3000)
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Kiểm tra xem người dùng đã đăng nhập hay chưa
if (!isset($_SESSION["user_id"])) {
    echo json_encode(["message" => "Chưa đăng nhập."]);
    exit;
}

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    // Lấy từ khóa tìm kiếm từ query string
    $keyword = $_GET["keyword"] ?? "";

    // Kiểm tra nếu không có từ khóa
    if (empty($keyword)) {
        echo json_encode(["message" => "Vui lòng cung cấp từ khóa tìm kiếm."]);
        exit;
    }

    // Tìm kiếm ghi chú theo tiêu đề và nội dung
    $sql = "SELECT * FROM notes  
        WHERE user_id = ? 
        AND (title LIKE ? OR content LIKE ? OR tags LIKE ? OR category LIKE ?) 
        ORDER BY is_pinned DESC, GREATEST(modified_at, created_at) DESC 
        LIMIT 10";
    $stmt = $pdo->prepare($sql);
    $searchTerm = "%" . $keyword . "%";
    $stmt->execute([$user_id, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($notes);
} else {
    echo json_encode(["message" => "Phương thức không hợp lệ."]);
}
