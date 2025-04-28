<?php

require "config.php"; // Kết nối database
session_start();


// 🔥 Thêm header để bật CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Trả về JSON
header("Content-Type: application/json");


if (!isset($_SESSION["user_id"])) {
    echo json_encode(["message" => "Chưa đăng nhập."]);
    exit;
}

//Lấy user_id từ session
$user_id = $_SESSION["user_id"];

// Kiểm tra và lọc tag từ yêu cầu GET
$tag = isset($_GET["tag"]) ? trim($_GET["tag"]) : "";
// Thêm ký tự % để sử dụng với LIKE
$tag = "%" . $tag . "%";

// Chuẩn bị truy vấn để lấy danh sách ghi chú theo tag
$sql = "SELECT * FROM notes WHERE tags LIKE ? AND user_id = ? ORDER BY is_pinned DESC, GREATEST(modified_at, created_at) DESC";
$stmt = $pdo->prepare($sql);

// Thực thi truy vấn
$stmt->execute([$tag, $user_id]);

// Lấy kết quả và xử lý đường dẫn ảnh
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($notes as &$note) {
    // Giải mã chuỗi JSON của ảnh nếu cần
    if (!empty($note["image"])) {
        $note["image"] = json_decode($note["image"], true); // Chuyển đổi chuỗi JSON thành mảng
    }
}

// Trả về kết quả dưới dạng JSON
echo json_encode($notes);
exit;
