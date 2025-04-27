<?php
require 'config.php';
session_start();


// 🔥 Thêm header để bật CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Trả về JSON
header('Content-Type: application/json');


// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['message' => 'Người dùng chưa đăng nhập.']);
    exit;
}

$userId = $_SESSION['user_id'];

// Xử lý yêu cầu GET để lấy cài đặt chủ đề
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare("SELECT theme FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $userSettings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode($userSettings);
    exit;
}

// Xử lý yêu cầu POST để tạo mới hoặc cập nhật cài đặt chủ đề
if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $data = json_decode(file_get_contents("php://input"), true);
    $userId = $data['user_id'] ?? null;
    $theme = $data['theme'] ?? null;

    if (!$userId || !$theme) {
        echo json_encode(['message' => 'Dữ liệu không hợp lệ.']);
        exit;
    }

    // Kiểm tra xem user có tồn tại không
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $exists = $stmt->fetchColumn();

    if ($exists) {
        // Cập nhật cài đặt chủ đề
        $stmt = $pdo->prepare("UPDATE users SET theme = ? WHERE id = ?");
        $stmt->execute([$theme, $userId]);
        echo json_encode(['message' => 'Cài đặt chủ đề đã được cập nhật.']);
    } else {
        echo json_encode(['message' => 'Người dùng không tồn tại.']);
    }

    exit;
}
?>