<?php

require "config.php";
session_start();


// 🔥 Thêm header để bật CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Trả về JSON
header("Content-Type: application/json");


$key = "12345";

function decodeNumber($encoded, $key)
{
    $encoded = str_replace(["-", "_"], ["+", "/"], $encoded); // Chuyển đổi về base64 thông thường
    $decoded = base64_decode($encoded);
    if ($decoded === false) {
        return false;
    }

    list($number, $hash) = explode("::", $decoded);
    $validHash = hash_hmac("sha256", $number, $key, true);

    return hash_equals($validHash, $hash) ? $number : false;
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check if note_id and access_password are provided as POST data
    if (isset($_POST["note_id"]) && isset($_POST["access_password"])) {
        $note_id_encode = $_GET["note_id"];
        $access_password = $_GET["access_password"];
        // $note_id = decodeNumber($note_id_encode,$key);
        $note_id = $note_id_encode;

    } else {
        echo json_encode(["message" => "Vui lòng cung cấp note_id và access_password."]);
        exit;
    }
} elseif ($_SERVER["REQUEST_METHOD"] === "GET") {
    // Check if note_id and access_password are provided as GET parameters
    if (isset($_GET["note_id"]) && isset($_GET["access_password"])) {
        $note_id_encode = $_GET["note_id"];
        $access_password = $_GET["access_password"];
        // $note_id = decodeNumber($note_id_encode,$key);
        $note_id = $note_id_encode;

    } else {
        echo json_encode(["message" => "Vui lòng cung cấp note_id và access_password."]);
        exit;
    }
} else {
    echo json_encode(["message" => "Phương thức không hợp lệ."]);
    exit;
}

// Check the password in the database
$stmt = $pdo->prepare("SELECT permission, access_password FROM shared_notes WHERE note_id = ? AND access_password = ?");
$stmt->execute([$note_id, $access_password]);
$shared_note = $stmt->fetch(PDO::FETCH_ASSOC);

if ($shared_note) {
    // Query to get the specific note
    $stmt = $pdo->prepare("SELECT id, user_id, title, content, created_at, modified_at, is_pinned, category, tags, password, image, font_size, note_color FROM notes WHERE id = ?");
    $stmt->execute([$note_id]);
    $note = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($note) {
        // Get access permission
        $permission = $shared_note["permission"];

        $note_data = [
            "id" => $note["id"],
            "title" => $note["title"],
            "content" => $note["content"],
            "created_at" => $note["created_at"],
            "modified_at" => $note["modified_at"],
            "user_id" => $note["user_id"],
            "is_pinned" => $note["is_pinned"],
            "category" => $note["category"],
            "tags" => $note["tags"],
            "permission" => $permission,
            "image" => $note["image"],
            "font_size" => $note["font_size"],
            "note_color" => $note["note_color"],
            "can_edit" => ($permission === "edit")
        ];

        // Return the note data as JSON
        header("Content-Type: application/json");
        echo json_encode(["message" => "✅ Truy cập thành công.", "note" => $note_data]);
    } else {
        echo json_encode(["message" => "❌  Ghi chú không đtồn tại hoặc không thuộc quyền truy cập."]);
    }
} else {
    echo json_encode(["message" => "❌ Mật khẩu không đúng."]);
}
