<?php

require_once "config.php";
session_start();

set_cors_header();
check_login();

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
