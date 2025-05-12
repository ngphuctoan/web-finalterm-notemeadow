<?php

require_once "config.php";
session_start();

set_cors_header();
check_login();

$userId = $_SESSION["user_id"];

// Xử lý yêu cầu GET để lấy cài đặt của ghi chú
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $noteId = $_GET["id"] ?? null;

    if (!$noteId) {
        echo json_encode(["message" => "Invalid note ID."]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT font_size, note_color FROM notes WHERE id = ? AND user_id = ?  ORDER BY is_pinned DESC, GREATEST(modified_at, created_at) DESC");
    $stmt->execute([$noteId, $userId]);
    $noteSettings = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($noteSettings) {
        echo json_encode($noteSettings);
    } else {
        echo json_encode(["message" => "Note not found."]);
    }
    exit;
}


if ($_SERVER["REQUEST_METHOD"] === "PATCH") {
    $data = json_decode(file_get_contents("php://input"), true);

    $noteId = $data["id"] ?? null;
    $userId = $data["user_id"] ?? null;
    $fontSize = $data["font_size"] ?? null;
    $noteColor = $data["note_color"] ?? null;

    if (!$noteId || !$userId) {
        echo json_encode(["message" => "Invalid note ID or user ID."]);
        exit;
    }

    // Kiểm tra xem ghi chú có tồn tại không
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notes WHERE id = ? AND user_id = ?");
    $stmt->execute([$noteId, $userId]);
    $exists = $stmt->fetchColumn();

    if (!$exists) {
        echo json_encode(["message" => "Note not found."]);
        exit;
    }

    // Xây dựng câu lệnh SQL động
    $fields = [];
    $params = [];

    if ($fontSize) {
        $fields[] = "font_size = ?";
        $params[] = $fontSize;
    }
    if ($noteColor) {
        $fields[] = "note_color = ?";
        $params[] = $noteColor;
    }

    if (empty($fields)) {
        echo json_encode(["message" => "No data to update."]);
        exit;
    }

    $query = "UPDATE notes SET " . implode(", ", $fields) . " WHERE id = ? AND user_id = ?";
    $params[] = $noteId;
    $params[] = $userId;

    $stmt = $pdo->prepare($query);
    if ($stmt->execute($params)) {
        echo json_encode(["message" => "Note settings have been updated."]);
    } else {
        echo json_encode(["message" => "Error updating settings."]);
    }
}
