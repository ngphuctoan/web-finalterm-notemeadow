<?php
require 'config.php';

// Định dạng phản hồi là JSON
header('Content-Type: application/json');
// Thêm CORS Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Kiểm tra phương thức gọi
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['message' => 'Phương thức không được hỗ trợ.']);
    exit;
}

// Kiểm tra token có tồn tại
if (!isset($_GET['token']) || empty($_GET['token'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['message' => 'Thiếu token kích hoạt.']);
    exit;
}

$token = $_GET['token'];

// Kiểm tra token có hợp lệ không
$stmt = $pdo->prepare("SELECT id, is_active FROM users WHERE activation_token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch();

if ($user) {
    if ($user['is_active']) {
        echo json_encode(['message' => 'Tài khoản đã được kích hoạt trước đó.']);
    } else {
        $update = $pdo->prepare("UPDATE users SET is_active = 1, activation_token = NULL WHERE id = ?");
        $update->execute([$user['id']]);
        echo json_encode(['message' => 'Tài khoản của bạn đã được kích hoạt thành công!']);
    }
} else {
    http_response_code(404); // Not Found
    echo json_encode(['message' => 'Token không hợp lệ hoặc đã được sử dụng.']);
}
?>
