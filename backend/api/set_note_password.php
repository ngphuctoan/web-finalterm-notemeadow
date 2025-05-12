<?php

require "config.php";
session_start();

set_cors_header();
check_login();

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
