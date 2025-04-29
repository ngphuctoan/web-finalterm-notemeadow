<?php
require 'config.php'; // Kết nối database
session_start();

// 🔥 Thêm header để bật CORS
header("Access-Control-Allow-Origin: http://localhost:1234");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Trả về JSON
header('Content-Type: application/json');


// if (!isset($_SESSION['user_id'])) {
//     echo json_encode(['message' => 'Chưa đăng nhập.']);
//     exit;
// }

//Lấy user_id từ session
$user_id = $_SESSION['user_id'] ?? 18;

// Kiểm tra và lọc keyword từ yêu cầu GET
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
// Thêm ký tự % để sử dụng với LIKE
$keyword = '%' . $keyword . '%';

// Chuẩn bị truy vấn để lấy danh sách ghi chú theo keyword
$sql = "SELECT * FROM notes  
        WHERE user_id = ? 
        AND (title LIKE ? OR content LIKE ? OR tags LIKE ? OR category LIKE ?) 
        ORDER BY is_pinned DESC, GREATEST(modified_at, created_at) DESC 
        LIMIT 10";

// Chuẩn bị và thực thi câu lệnh
$stmt = $pdo->prepare($sql);

$stmt->execute([$user_id, $keyword, $keyword, $keyword, $keyword]);
// Lấy kết quả và xử lý đường dẫn ảnh
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($notes as &$note) {
    // Giải mã chuỗi JSON của ảnh nếu cần
    if (!empty($note['image'])) {
        $note['image'] = json_decode($note['image'], true); // Chuyển đổi chuỗi JSON thành mảng
    }
}

// Trả về kết quả dưới dạng JSON
echo json_encode($notes);
exit;