<?php

require "config.php"; // Kết nối cơ sở dữ liệu

set_cors_header();

// Kiểm tra phương thức yêu cầu
if ($_SERVER["REQUEST_METHOD"] === "DELETE") {
    // Nhận dữ liệu từ body
    $data = json_decode(file_get_contents("php://input"));

    // Lấy user_id từ dữ liệu
    $user_id = $data->user_id ?? null;

    if (!$user_id) {
        echo json_encode(["message" => "user_id is required."]);
        exit;
    }

    // Truy vấn để lấy thông tin người dùng
    $stmt = $pdo->prepare("SELECT image FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Nếu người dùng tồn tại
    if ($user) {
        // Xóa tệp ảnh nếu có
        if ($user["image"] && file_exists($user["image"])) {
            unlink($user["image"]); // Xóa tệp ảnh
        }

        // Xóa người dùng khỏi cơ sở dữ liệu
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$user_id])) {
            echo json_encode(["message" => "User has been successfully deleted."]);
        } else {
            echo json_encode(["message" => "Unable to delete user."]);
        }
    } else {
        echo json_encode(["message" => "User does not exist."]);
    }
} else {
    echo json_encode(["message" => "Invalid method."]);
}
