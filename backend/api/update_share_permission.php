<?php

require "config.php";
session_start();


// 🔥 Thêm header để bật CORS
header("Access-Control-Allow-Origin: http://localhost:1234");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Trả về JSON
header("Content-Type: application/json");


if (!isset($_SESSION["user_id"])) {
    echo json_encode(["message" => "Not logged in."]);
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
            echo json_encode(["message" => "Permission has been updated successfully."]);
        } else {
            echo json_encode(["message" => "Failed to update permission."]);
        }
    } else {
        echo json_encode(["message" => "Please provide note_id, recipient_email and permission."]);
    }
} else {
    echo json_encode(["message" => "Invalid method."]);
}
