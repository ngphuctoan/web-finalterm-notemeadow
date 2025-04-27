<?php
require 'config.php';
session_start();

// 🔥 Thêm header để bật CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Trả về JSON
header('Content-Type: application/json');

// Kiểm tra phiên đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập.']);
    exit;
}

$user_id = $_SESSION['user_id']; // Lấy user_id từ session
// Kiểm tra phương thức yêu cầu
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true); // Lấy dữ liệu từ yêu cầu JSON

    // Kiểm tra dữ liệu đầu vào
    if (empty($data['note_id'])) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng cung cấp note_id hợp lệ.']);
        exit;
    }

    $note_id = $data['note_id'];

    try {
        // Kiểm tra xem ghi chú có tồn tại và thuộc về user hiện tại không
        $stmt = $pdo->prepare("SELECT id FROM notes WHERE id = ? AND user_id = ?");
        $stmt->execute([$note_id, $user_id]);
        $note = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$note) {
            echo json_encode(['success' => false, 'message' => 'Ghi chú không tồn tại hoặc bạn không có quyền xóa.']);
            exit;
        }

        // Tiến hành xóa ghi chú
        $stmt = $pdo->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$note_id, $user_id])) {
            echo json_encode(['success' => true, 'message' => 'Ghi chú đã được xóa thành công.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Xóa ghi chú không thành công.']);
        }
    } catch (PDOException $e) {
        error_log("❌ Lỗi SQL: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa dữ liệu: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
}
?>
