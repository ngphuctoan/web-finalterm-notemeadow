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

// Xử lý yêu cầu GET để lấy cài đặt của ghi chú
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $noteId = $_GET['id'] ?? null;

    if (!$noteId) {
        echo json_encode(['message' => 'ID ghi chú không hợp lệ.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT font_size, note_color FROM notes WHERE id = ? AND user_id = ?  ORDER BY is_pinned DESC, GREATEST(modified_at, created_at) DESC");
    $stmt->execute([$noteId, $userId]);
    $noteSettings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($noteSettings) {
        echo json_encode($noteSettings);
    } else {
        echo json_encode(['message' => 'Ghi chú không tìm thấy.']);
    }
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $noteId = $data['id'] ?? null;
    $userId = $data['user_id'] ?? null;
    $fontSize = $data['font_size'] ?? null;
    $noteColor = $data['note_color'] ?? null;

    if (!$noteId || !$userId) {
        echo json_encode(['message' => 'ID ghi chú hoặc user_id không hợp lệ.']);
        exit;
    }

    // Kiểm tra xem ghi chú có tồn tại không
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notes WHERE id = ? AND user_id = ?");
    $stmt->execute([$noteId, $userId]);
    $exists = $stmt->fetchColumn();

    if (!$exists) {
        echo json_encode(['message' => 'Ghi chú không tìm thấy.']);
        exit;
    }

    // Xây dựng câu lệnh SQL động
    $fields = [];
    $params = [];

    if ($fontSize) {
        $fields[] = "font_size = ?";
        $params[] = $fontSize;
    }
    if ($noteColor) {
        $fields[] = "note_color = ?";
        $params[] = $noteColor;
    }

    if (empty($fields)) {
        echo json_encode(['message' => 'Không có dữ liệu cần cập nhật.']);
        exit;
    }

    $query = "UPDATE notes SET " . implode(", ", $fields) . " WHERE id = ? AND user_id = ?";
    $params[] = $noteId;
    $params[] = $userId;

    $stmt = $pdo->prepare($query);
    if ($stmt->execute($params)) {
        echo json_encode(['message' => 'Cài đặt ghi chú đã được cập nhật.']);
    } else {
        echo json_encode(['message' => 'Lỗi khi cập nhật cài đặt.']);
    }
}

?>