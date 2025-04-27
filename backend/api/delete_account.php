<?php
require 'config.php'; // Kết nối cơ sở dữ liệu

// 🔥 Thêm header để bật CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Trả về JSON
header('Content-Type: application/json');

// Kiểm tra phương thức yêu cầu
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Nhận dữ liệu từ body
    $data = json_decode(file_get_contents("php://input"));

    // Lấy user_id từ dữ liệu
    $user_id = $data->user_id ?? null;

    if (!$user_id) {
        echo json_encode(['message' => 'user_id là bắt buộc.']);
        exit;
    }

    // Truy vấn để lấy thông tin người dùng
    $stmt = $pdo->prepare("SELECT image FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Nếu người dùng tồn tại
    if ($user) {
        // Xóa tệp ảnh nếu có
        if ($user['image'] && file_exists($user['image'])) {
            unlink($user['image']); // Xóa tệp ảnh
        }

        // Xóa người dùng khỏi cơ sở dữ liệu
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$user_id])) {
            echo json_encode(['message' => 'Người dùng đã được xóa thành công.']);
        } else {
            echo json_encode(['message' => 'Không thể xóa người dùng.']);
        }
    } else {
        echo json_encode(['message' => 'Người dùng không tồn tại.']);
    }
} else {
    echo json_encode(['message' => 'Phương thức không hợp lệ.']);
}
?>