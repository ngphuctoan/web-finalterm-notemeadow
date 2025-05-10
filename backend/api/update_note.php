<?php

require "config.php";
session_start();

// Enable CORS
header("Access-Control-Allow-Origin: http://localhost:1234");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Log raw input for debugging
$rawInput = file_get_contents("php://input");
error_log("📥 Dữ liệu thô nhận được: $rawInput");

$data = json_decode($rawInput, true) ?? $_POST;
error_log("📥 Dữ liệu sau xử lý: " . json_encode($data));

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Chưa đăng nhập."]);
    exit;
}

$user_id = $_SESSION["user_id"];
$note_id = $data["note_id"] ?? null;

if (empty($note_id)) {
    echo json_encode(["success" => false, "message" => "Vui lòng cung cấp note_id hợp lệ."]);
    exit;
}

// Check permission
$permissionStmt = $pdo->prepare("
    SELECT sn.permission
    FROM shared_notes sn
    WHERE sn.note_id = ? AND sn.recipient_email = (SELECT email FROM users WHERE id = ?)
");
$permissionStmt->execute([$note_id, $user_id]);
$permission = $permissionStmt->fetchColumn();

$ownerStmt = $pdo->prepare("SELECT user_id FROM notes WHERE id = ?");
$ownerStmt->execute([$note_id]);
$owner_id = $ownerStmt->fetchColumn();

if ($permission !== "edit" && $owner_id !== $user_id) {
    echo json_encode(["success" => false, "message" => "Bạn không có quyền chỉnh sửa ghi chú này."]);
    exit;
}

// Fields to update
$updateFields = ["title", "content", "is_pinned", "category", "tags", "password", "image", "font_size", "note_color"];
$fields = [];
$params = [];

// Handle image upload
$imagePaths = [];
if (isset($_FILES["image"])) {
    foreach ($_FILES["image"]["tmp_name"] as $i => $tmp) {
        $name = $_FILES["image"]["name"][$i];
        $error = $_FILES["image"]["error"][$i];
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        if ($error === UPLOAD_ERR_OK && in_array($ext, ["jpg", "jpeg", "png", "gif"])) {
            $dest = "uploads/" . basename($name);
            if (move_uploaded_file($tmp, $dest)) {
                $imagePaths[] = $dest;
            } else {
                echo json_encode(["success" => false, "message" => "Lỗi tải ảnh lên."]);
                exit;
            }
        } elseif (!in_array($ext, ["jpg", "jpeg", "png", "gif"])) {
            echo json_encode(["success" => false, "message" => "Chỉ cho phép tải lên ảnh."]);
            exit;
        }
    }
}

// Append valid fields
foreach ($updateFields as $field) {
    if (isset($data[$field])) {
        $fields[] = "$field = ?";
        $params[] = $data[$field];
    }
}

if (!empty($imagePaths)) {
    $fields[] = "image = ?";
    $params[] = implode(",", $imagePaths);
}

$params[] = $note_id;

$updateStmt = $pdo->prepare("UPDATE notes SET " . implode(", ", $fields) . " WHERE id = ?");
$success = $updateStmt->execute($params);

if ($success) {
    $historyStmt = $pdo->prepare("INSERT INTO note_history (note_id, user_id, action) VALUES (?, ?, ?)");
    $historyStmt->execute([$note_id, $user_id, "Đã chỉnh sửa ghi chú"]);

    echo json_encode(["success" => true, "message" => "Cập nhật ghi chú thành công."]);
} else {
    echo json_encode(["success" => false, "message" => "Cập nhật không thành công."]);
}