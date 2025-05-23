<?php

require_once "config.php"; // Kết nối cơ sở dữ liệu
session_start();

set_cors_header();
check_login();

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    // Lấy từ khóa tìm kiếm từ query string
    $keyword = $_GET["keyword"] ?? "";

    // Kiểm tra nếu không có từ khóa
    if (empty($keyword)) {
        echo json_encode(["message" => "Please provide a search keyword."]);
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
    echo json_encode(["message" => "Invalid method."]);
}
