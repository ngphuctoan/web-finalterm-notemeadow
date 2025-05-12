<?php

require "config.php";
session_start();

set_cors_header();
check_login();

$user_id = $_SESSION["user_id"]; // Lấy user_id từ session
// Kiểm tra phương thức yêu cầu
if ($_SERVER["REQUEST_METHOD"] === "DELETE") {
    $data = json_decode(file_get_contents("php://input"), true); // Lấy dữ liệu từ yêu cầu JSON

    // Kiểm tra dữ liệu đầu vào
    if (empty($data["note_id"])) {
        echo json_encode(["success" => false, "message" => "Please provide a valid note_id."]);
        exit;
    }

    $note_id = $data["note_id"];

    try {
        // Kiểm tra xem ghi chú có tồn tại và thuộc về user hiện tại không
        $stmt = $pdo->prepare("SELECT id FROM notes WHERE id = ? AND user_id = ?");
        $stmt->execute([$note_id, $user_id]);
        $note = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$note) {
            echo json_encode(["success" => false, "message" => "Note does not exist or you don't have permission to delete it."]);
            exit;
        }

        // Tiến hành xóa ghi chú
        $stmt = $pdo->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$note_id, $user_id])) {
            echo json_encode(["success" => true, "message" => "Note has been deleted successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to delete note."]);
        }
    } catch (PDOException $e) {
        error_log("❌ SQL Error: " . $e->getMessage());
        echo json_encode(["success" => false, "message" => "Error deleting data: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid method."]);
}
