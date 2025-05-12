<?php

require "config.php"; // Kết nối tới cơ sở dữ liệu
require "send_email.php"; // Nhúng tệp gửi email
session_start();

// 🔥 Thêm header để bật CORS
header("Access-Control-Allow-Origin: http://localhost:1234");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Trả về JSON
header("Content-Type: application/json");

$key = "12345";

function generateRandomPassword($length = 10) {
    return bin2hex(random_bytes($length / 2));
}

function encodeNumber($number, $key) {
    $hash = hash_hmac("sha256", $number, $key, true);
    $encoded = base64_encode($number . "::" . $hash);
    return str_replace(["+", "/", "="], ["-", "_", ""], $encoded); // Chuyển đổi base64 thành URL-safe base64
}

// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION["user_id"])) {
    echo json_encode(["message" => "Chưa đăng nhập."]);
    exit;
}

// PUT - Chia sẻ hoặc cập nhật quyền truy cập ghi chú
if ($_SERVER["REQUEST_METHOD"] === "PUT") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data["note_id"], $data["recipients"]) || !is_array($data["recipients"])) {
        echo json_encode(["message" => "Thiếu note_id hoặc recipients không hợp lệ."]);
        exit;
    }

    $note_id = $data["note_id"];
    $recipients_data = $data["recipients"];
    $shared_by = $_SESSION["user_id"];
    $email_send = $_SESSION["user_email"];
    $responses = [];

    // Fetch old recipients to determine which to revoke
    $stmt = $pdo->prepare("SELECT recipient_email FROM shared_notes WHERE note_id = ? AND shared_by = ?");
    $stmt->execute([$note_id, $shared_by]);
    $existing_emails = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $new_emails = array_column($recipients_data, "recipient");
    $to_revoke = array_diff($existing_emails, $new_emails);

    // Revoke those no longer in the list
    foreach ($to_revoke as $email) {
        $deleteStmt = $pdo->prepare("DELETE FROM shared_notes WHERE note_id = ? AND recipient_email = ?");
        if ($deleteStmt->execute([$note_id, $email])) {
            $historyStmt = $pdo->prepare("INSERT INTO note_history (note_id, user_id, action) VALUES (?, ?, ?)");
            $action = "Đã thu hồi quyền chia sẻ ghi chú với $email";
            $historyStmt->execute([$note_id, $shared_by, $action]);
        }
    }

    foreach ($recipients_data as $entry) {
        $recipient_email = $entry["recipient"] ?? null;
        $permission = trim($entry["permission"] ?? "");

        if (!in_array($permission, ["read", "edit"], true)) {
            $responses[] = ["email" => $recipient_email, "message" => "Quyền không hợp lệ: $permission"];
            continue;
        }

        if (!filter_var($recipient_email, FILTER_VALIDATE_EMAIL) || !$permission) {
            $responses[] = ["email" => $recipient_email, "message" => "Email hoặc quyền không hợp lệ."];
            continue;
        }

        // Update if exists
        $checkStmt = $pdo->prepare("SELECT id FROM shared_notes WHERE note_id = ? AND recipient_email = ?");
        $checkStmt->execute([$note_id, $recipient_email]);
        $row = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $updateStmt = $pdo->prepare("UPDATE shared_notes SET permission = ? WHERE id = ?");
            $updateStmt->execute([$permission, $row["id"]]);
            $responses[] = ["email" => $recipient_email, "message" => "Đã cập nhật quyền truy cập."];
        } else {
            $access_password = generateRandomPassword();
            $insertStmt = $pdo->prepare("
                INSERT INTO shared_notes (note_id, recipient_email, permission, access_password, shared_by, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            if ($insertStmt->execute([$note_id, $recipient_email, $permission, $access_password, $shared_by])) {
                // Gửi email + ghi lịch sử như cũ
                $token = encodeNumber($note_id, $key);
                $note_link = "http://localhost:1234/#/edit/" . $note_id;
                $url = "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($note_link) . "&size=200x200";
                $subject = "A note has been shared with you - $email_send";
                $body = <<<EOD
                <!DOCTYPE html>
                <html lang="en">
                <head>
                <meta charset="UTF-8" />
                <title>Email Share Note</title>
                <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600&display=swap" rel="stylesheet" />
                </head>
                <body style="font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f9fafb; padding: 20px; color: #111827; line-height: 1.6;">
                <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                    <h2 style="color: #d89614; margin-bottom: 16px;">Hello, <span style="color: #111827;">$recipient_email!</span></h2>

                    <p style="margin-bottom: 12px;">
                    You have been invited to view a note with <strong>$permission</strong> permissions.
                    </p>

                    <p style="margin-bottom: 12px;">
                    Click the button below or scan the QR code to access the note:
                    </p>

                    <div style="text-align: center; margin: 24px 0;">
                    <img src="$url" alt="QR Code" style="max-width: 200px; border: 1px solid #e5e7eb; padding: 8px; border-radius: 8px;" />
                    <br />
                    <a href="$note_link" style="display: inline-block; margin-top: 16px; background-color: #d89614; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600;">
                        Open Note
                    </a>
                    </div>

                    <p style="margin-bottom: 8px;">
                    <strong>Password:</strong> <span style="background: #f3f4f6; padding: 4px 8px; border-radius: 6px;">$access_password</span>
                    </p>

                    <p style="margin-top: 32px; color: #6b7280;">
                    Best regards,<br />
                    <strong>NoteMeadow Team</strong>
                    </p>
                </div>
                </body>
                </html>
                EOD;

                if (sendEmail($recipient_email, $subject, $body)) {
                    $historyStmt = $pdo->prepare("INSERT INTO note_history (note_id, user_id, action) VALUES (?, ?, ?)");
                    $action = "Đã chia sẻ ghi chú với $recipient_email";
                    $historyStmt->execute([$note_id, $shared_by, $action]);

                    $responses[] = ["email" => $recipient_email, "message" => "Đã gửi email chia sẻ."];
                } else {
                    $responses[] = ["email" => $recipient_email, "message" => "Chia sẻ thành công nhưng không gửi được email."];
                }
            } else {
                $responses[] = ["email" => $recipient_email, "message" => "Không thể chia sẻ ghi chú."];
            }
        }
    }

    echo json_encode($responses);
    exit;
}

// Lấy danh sách ghi chú bạn đã chia sẻ
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"]) && $_GET["action"] === "shared_by_me") {
    $user_id = $_SESSION["user_id"];
    $stmt = $pdo->prepare("
        SELECT sn.id as shared_id, sn.recipient_email, sn.created_at, sn.permission, sn.access_password as password, n.id
        FROM shared_notes sn
        JOIN notes n ON n.id = sn.note_id
        WHERE sn.shared_by = ?
    ");
    $stmt->execute([$user_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// Lấy danh sách ghi chú được chia sẻ với bạn
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"]) && $_GET["action"] === "shared_with_me") {
    $email = $_SESSION["user_email"];
    $stmt = $pdo->prepare("
        SELECT sn.id as shared_id, sn.created_at, sn.permission, sn.access_password as password,
        n.id, n.title, n.content, n.note_color, n.font_size,
        u.display_name as shared_by
        FROM shared_notes sn
        JOIN notes n ON n.id = sn.note_id
        JOIN users u ON sn.shared_by = u.id
        WHERE recipient_email = ?
    ");
    $stmt->execute([$email]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}