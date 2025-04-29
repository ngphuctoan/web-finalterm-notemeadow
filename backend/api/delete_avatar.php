<?php
require 'config.php'; // Kết nối cơ sở dữ liệu
session_start();

// 🔥 Thêm header để bật CORS
header("Access-Control-Allow-Origin: http://localhost:1234");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Trả về JSON
header('Content-Type: application/json');

// Kiểm tra phiên đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['message' => 'Chưa đăng nhập.']);
    exit;
}

// Lấy user_id từ session
$user_id = $_SESSION['user_id'];

// Kiểm tra phương thức yêu cầu
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Cập nhật hình ảnh mặc định
    $defaultImage = 'https://cdn-icons-png.flaticon.com/512/9187/9187604.png'; // Đường dẫn hình ảnh mặc định
    $stmt = $pdo->prepare("UPDATE users SET image = ? WHERE id = ?");
    
    if ($stmt->execute([$defaultImage, $user_id])) {
        echo json_encode(['message' => 'Đã xóa ảnh đại diện hiện tại.']);
    } else {
        echo json_encode(['message' => 'Không thể cập nhật thông tin người dùng.']);
    }
} else {
    echo json_encode(['message' => 'Phương thức không hợp lệ.']);
}
?>