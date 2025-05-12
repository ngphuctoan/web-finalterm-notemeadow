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

    if (isset($data["note_id"]) && isset($data["password"])) {
        $note_id = $data["note_id"];
        $password = password_hash($data["password"], PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("UPDATE notes SET password = ? WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$password, $note_id, $_SESSION["user_id"]])) {
            echo json_encode(["message" => "Note password has been set."]);
        } else {
            echo json_encode(["message" => "Failed to set note password."]);
        }
    } else {
        echo json_encode(["message" => "Please provide note_id and password."]);
    }
}
