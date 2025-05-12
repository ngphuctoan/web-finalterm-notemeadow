<?php

require "config.php";
session_start();

set_cors_header();
check_login();

$userId = $_SESSION["user_id"];

// Xử lý yêu cầu GET để lấy cài đặt chủ đề
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $stmt = $pdo->prepare("SELECT theme FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $userSettings = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($userSettings);
    exit;
}

// Xử lý yêu cầu POST để tạo mới hoặc cập nhật cài đặt chủ đề
if ($_SERVER["REQUEST_METHOD"] === "PATCH") {
    $data = json_decode(file_get_contents("php://input"), true);
    $userId = $data["user_id"] ?? null;
    $theme = $data["theme"] ?? null;

    if (!$userId || !$theme) {
        echo json_encode(["message" => "Invalid data."]);
        exit;
    }

    // Kiểm tra xem user có tồn tại không
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $exists = $stmt->fetchColumn();

    if ($exists) {
        // Cập nhật cài đặt chủ đề
        $stmt = $pdo->prepare("UPDATE users SET theme = ? WHERE id = ?");
        $stmt->execute([$theme, $userId]);
        echo json_encode(["message" => "Theme settings have been updated."]);
    } else {
        echo json_encode(["message" => "User does not exist."]);
    }

    exit;
}
