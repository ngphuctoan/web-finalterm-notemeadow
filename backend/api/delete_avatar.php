<?php

require "config.php"; // Kết nối cơ sở dữ liệu
session_start();

set_cors_header();
check_login();

// Lấy user_id từ session
$user_id = $_SESSION["user_id"];

// Kiểm tra phương thức yêu cầu
if ($_SERVER["REQUEST_METHOD"] === "DELETE") {
    // Cập nhật hình ảnh mặc định
    $defaultImage = ""; // Đường dẫn hình ảnh mặc định
    $stmt = $pdo->prepare("UPDATE users SET image = ? WHERE id = ?");

    if ($stmt->execute([$defaultImage, $user_id])) {
        echo json_encode(["message" => "Current avatar has been removed."]);
    } else {
        echo json_encode(["message" => "Unable to update user information."]);
    }
} else {
    echo json_encode(["message" => "Invalid method."]);
}
