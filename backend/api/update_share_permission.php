<?php

require "config.php";
session_start();


// 🔥 Thêm header để bật CORS
header("Access-Control-Allow-Origin: http://localhost:1234");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Trả về JSON
header("Content-Type: application/json");


if (!isset($_SESSION["user_id"])) {
    echo json_encode(["message" => "Chưa đăng nhập."]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data["note_id"]) && isset($data["recipient_email"]) && isset($data["permission"])) {
        $note_id = $data["note_id"];
        $recipient_email = $data["recipient_email"];
        $permission = $data["permission"];

        $stmt = $pdo->prepare("UPDATE shared_notes SET permission = ? WHERE note_id = ? AND recipient_email = ?");
        if ($stmt->execute([$permission, $note_id, $recipient_email])) {
            echo json_encode(["message" => "Quyền đã được cập nhật thành công."]);
        } else {
            echo json_encode(["message" => "Cập nhật quyền không thành công."]);
        }
    } else {
        echo json_encode(["message" => "Vui lòng cung cấp note_id, recipient_email và permission."]);
    }
} else {
    echo json_encode(["message" => "Phương thức không hợp lệ."]);
}
