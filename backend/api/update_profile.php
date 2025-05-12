<?php

require "config.php"; // Kết nối cơ sở dữ liệu
session_start();

set_cors_header();
check_login();

// Kiểm tra phương thức
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed."]);
    exit;
}

try {
    $data = json_decode(file_get_contents("php://input"), true);
    $user_id = $_SESSION["user_id"];
    $name = $data["name"] ?? "";
    $email = $data["email"] ?? "";

    // Kiểm tra dữ liệu
    if (empty($name) || empty($email)) {
        http_response_code(400);
        echo json_encode(["message" => "Name and email are required."]);
        exit;
    }

    // Kiểm tra email hợp lệ
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid email format."]);
        exit;
    }

    // Kiểm tra email đã tồn tại chưa (trừ email hiện tại của user)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(["message" => "Email already exists."]);
        exit;
    }

    // Cập nhật thông tin
    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $stmt->execute([$name, $email, $user_id]);

    echo json_encode(["message" => "User information has been updated."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Error updating profile: " . $e->getMessage()]);
}
