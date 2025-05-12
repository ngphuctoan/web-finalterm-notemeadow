<?php

require_once "config.php";

set_cors_header();

// Kiểm tra phương thức gọi
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["message" => "Method not supported."]);
    exit;
}

// Kiểm tra token có tồn tại
if (!isset($_GET["token"]) || empty($_GET["token"])) {
    http_response_code(400); // Bad Request
    echo json_encode(["message" => "Missing activation token."]);
    exit;
}

$token = $_GET["token"];

// Kiểm tra token có hợp lệ không
$stmt = $pdo->prepare("SELECT id, is_active FROM users WHERE activation_token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch();

if ($user) {
    if ($user["is_active"]) {
        echo json_encode(["message" => "Account has already been activated."]);
    } else {
        $update = $pdo->prepare("UPDATE users SET is_active = 1, activation_token = NULL WHERE id = ?");
        $update->execute([$user["id"]]);
        echo json_encode(["message" => "Your account has been successfully activated!"]);
    }
} else {
    http_response_code(404); // Not Found
    echo json_encode(["message" => "Invalid token or token has already been used."]);
}
