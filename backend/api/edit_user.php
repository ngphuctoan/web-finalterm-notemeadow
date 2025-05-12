<?php

require "config.php"; // Kết nối cơ sở dữ liệu
session_start();

set_cors_header();
check_login();

// Lấy user_id từ session
$user_id = $_SESSION["user_id"];

// Kiểm tra phương thức yêu cầu
if ($_SERVER["REQUEST_METHOD"] !== "PUT") {
    http_response_code(405);
    echo json_encode(["message" => "Method Not Allowed."]);
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
    echo json_encode(["message" => "Please provide at least one field to update."]);
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
        echo json_encode(["message" => "User information has been updated."]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to update information."]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Error updating data: " . htmlspecialchars($e->getMessage())]);
}
