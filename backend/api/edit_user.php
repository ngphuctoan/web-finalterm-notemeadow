<?php

require "config.php"; // Kết nối cơ sở dữ liệu
session_start();


// 🔥 Thêm header để bật CORS
header("Access-Control-Allow-Origin: http://localhost:1234");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Trả về JSON
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Tell the browser it's okay
    header("Access-Control-Allow-Origin: http://localhost:1234");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    http_response_code(200);
    exit;
}

// Kiểm tra phiên đăng nhập
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["message" => "Chưa đăng nhập."]);
    exit;
}

// Lấy user_id từ session
$user_id = $_SESSION["user_id"];

// Kiểm tra phương thức yêu cầu
if ($_SERVER["REQUEST_METHOD"] !== "PUT") {
    http_response_code(405);
    echo json_encode(["message" => "Phương thức không hợp lệ."]);
    exit;
}

// Lấy dữ liệu JSON từ yêu cầu
$data = json_decode(file_get_contents("php://input"), true);

// Khởi tạo mảng để lưu các trường cần cập nhật
$updateFields = [];
$params = [];

// Kiểm tra và thêm các trường vào mảng cập nhật
if (isset($data["email"])) {
    $updateFields[] = "email = ?";
    $params[] = $data["email"];
}
if (isset($data["display_name"])) {
    $updateFields[] = "display_name = ?";
    $params[] = $data["display_name"];
}
if (isset($data["is_active"])) {
    $updateFields[] = "is_active = ?";
    $params[] = $data["is_active"];
}
if (isset($data["activation_token"])) {
    $updateFields[] = "activation_token = ?";
    $params[] = $data["activation_token"];
}
if (isset($data["preferences"])) {
    $updateFields[] = "preferences = ?";
    $params[] = $data["preferences"]; // Chuyển đổi thành JSON nếu cần
}
if (isset($data["image"])) {
    $updateFields[] = "image = ?";
    $params[] = $data["image"]; // Lưu ảnh dưới dạng JSON
}
if (isset($data["theme"])) {
    $updateFields[] = "theme = ?";
    $params[] = $data["theme"];
}

// Nếu không có trường nào được cung cấp để cập nhật
if (empty($updateFields)) {
    http_response_code(400);
    echo json_encode(["message" => "Vui lòng cung cấp ít nhất một trường để cập nhật."]);
    exit;
}

// Thêm user_id vào cuối mảng params
$params[] = $user_id;

// Tạo câu lệnh SQL động
$sql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ?";
$stmt = $pdo->prepare($sql);

try {
    // Thực hiện câu lệnh cập nhật
    if ($stmt->execute($params)) {
        echo json_encode(["message" => "Thông tin người dùng đã được cập nhật."]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Cập nhật thông tin không thành công."]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Lỗi khi cập nhật dữ liệu: " . htmlspecialchars($e->getMessage())]);
}
