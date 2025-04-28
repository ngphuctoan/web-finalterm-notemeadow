<?php

require "config.php"; // Kết nối cơ sở dữ liệu
session_start();


// 🔥 Thêm header để bật CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Trả về JSON
header("Content-Type: application/json");


// Kiểm tra phiên đăng nhập
if (!isset($_SESSION["user_id"])) {
    echo json_encode(["message" => "Chưa đăng nhập."]);
    exit;
}

// Lấy user_id từ session
$user_id = $_SESSION["user_id"];

// Kiểm tra phương thức yêu cầu
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["image"])) {
    $target_dir = "uploads/";

    // Kiểm tra xem thư mục uploads có tồn tại không, nếu không thì tạo
    if (!is_dir($target_dir)) {
        if (!mkdir($target_dir, 0777, true)) {
            echo json_encode(["message" => "Không thể tạo thư mục uploads."]);
            exit;
        }
    }

    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Kiểm tra định dạng ảnh
    $allowed_types = ["jpg", "png", "jpeg", "gif"];
    if (!in_array($imageFileType, $allowed_types)) {
        echo json_encode(["message" => "Chỉ cho phép tải lên các định dạng JPG, JPEG, PNG."]);
        exit;
    }

    // Tải ảnh lên
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        // Cập nhật đường dẫn ảnh trong cơ sở dữ liệu
        $stmt = $pdo->prepare("UPDATE users SET image = ? WHERE id = ?");
        if ($stmt->execute([$target_file, $user_id])) {
            echo json_encode(["message" => "Ảnh đã được tải lên và cập nhật."]);
        } else {
            echo json_encode(["message" => "Cập nhật ảnh không thành công."]);
        }
    } else {
        echo json_encode(["message" => "Có lỗi khi tải ảnh lên."]);
    }
} else {
    echo json_encode(["message" => "Phương thức không hợp lệ hoặc không có tệp ảnh."]);
}
