<?php

require "config.php";
session_start();

set_cors_header();

// Log raw input for debugging
$rawInput = file_get_contents("php://input");
error_log("📥 Dữ liệu thô nhận được: $rawInput");

$data = json_decode($rawInput, true) ?? $_POST;
error_log("📥 Dữ liệu sau xử lý: " . json_encode($data));

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Not logged in."]);
    exit;
}

$user_id = $_SESSION["user_id"];
$note_id = $data["note_id"] ?? null;

if (empty($note_id)) {
    echo json_encode(["success" => false, "message" => "Please provide a valid note_id."]);
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
    echo json_encode(["success" => false, "message" => "You don't have permission to edit this note."]);
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
                echo json_encode(["success" => false, "message" => "Error uploading image."]);
                exit;
            }
        } elseif (!in_array($ext, ["jpg", "jpeg", "png", "gif"])) {
            echo json_encode(["success" => false, "message" => "Only image uploads are allowed."]);
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
    $historyStmt->execute([$note_id, $user_id, "Note has been edited"]);

    echo json_encode(["success" => true, "message" => "Note has been updated successfully."]);
} else {
    echo json_encode(["success" => false, "message" => "Update failed."]);
}