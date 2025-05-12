<?php

require_once "config.php"; // Kết nối tới cơ sở dữ liệu
require "send_email.php"; // Nhúng tệp gửi email
session_start();

set_cors_header();
check_login();

$key = "12345";

function generateRandomPassword($length = 10) {
    return bin2hex(random_bytes($length / 2));
}

function encodeNumber($number, $key) {
    $hash = hash_hmac("sha256", $number, $key, true);
    $encoded = base64_encode($number . "::" . $hash);
    return str_replace(["+", "/", "="], ["-", "_", ""], $encoded); // Chuyển đổi base64 thành URL-safe base64
}

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    echo json_encode(["message" => "Not logged in."]);
    exit;
}

// PUT - Share or update note access permissions
if ($_SERVER["REQUEST_METHOD"] === "PUT") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data["note_id"], $data["recipients"]) || !is_array($data["recipients"])) {
        echo json_encode(["message" => "Missing note_id or invalid recipients."]);
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
            $action = "Share permission has been revoked for $email";
            $historyStmt->execute([$note_id, $shared_by, $action]);
        }
    }

    foreach ($recipients_data as $entry) {
        $recipient_email = $entry["recipient"] ?? null;
        $permission = trim($entry["permission"] ?? "");

        if (!in_array($permission, ["read", "edit"], true)) {
            $responses[] = ["email" => $recipient_email, "message" => "Invalid permission: $permission"];
            continue;
        }

        if (!filter_var($recipient_email, FILTER_VALIDATE_EMAIL) || !$permission) {
            $responses[] = ["email" => $recipient_email, "message" => "Invalid email or permission."];
            continue;
        }

        // Update if exists
        $checkStmt = $pdo->prepare("SELECT id FROM shared_notes WHERE note_id = ? AND recipient_email = ?");
        $checkStmt->execute([$note_id, $recipient_email]);
        $row = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $updateStmt = $pdo->prepare("UPDATE shared_notes SET permission = ? WHERE id = ?");
            $updateStmt->execute([$permission, $row["id"]]);
            $responses[] = ["email" => $recipient_email, "message" => "Access permission has been updated."];
        } else {
            $access_password = generateRandomPassword();
            $insertStmt = $pdo->prepare("
                INSERT INTO shared_notes (note_id, recipient_email, permission, access_password, shared_by, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            if ($insertStmt->execute([$note_id, $recipient_email, $permission, $access_password, $shared_by])) {
                // Send email + record history
                $token = encodeNumber($note_id, $key);
                $note_link = "http://localhost:1234/#/edit/" . $note_id;
                $url = "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($note_link) . "&size=200x200";
                $subject = "A note has been shared with you - $email_send";
                $body = <<<EOD
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Email Share Note</title>
                </head>
                <body>
                    <h2>Hello, $recipient_email!</h2>
                    <p>You have been invited to view a note with $permission permissions.</p>
                    <p>Click the link below or scan the QR code to access the note:</p>
                    <img src="$url" alt="QR Code" />
                    <p><a href="$note_link">$note_link</a></p>
                    <p>Password: <strong>$access_password</strong></p>
                    <p>Best regards, the Note Website team.</p>
                </body>
                </html>
                EOD;

                if (sendEmail($recipient_email, $subject, $body)) {
                    $historyStmt = $pdo->prepare("INSERT INTO note_history (note_id, user_id, action) VALUES (?, ?, ?)");
                    $action = "Note has been shared with $recipient_email";
                    $historyStmt->execute([$note_id, $shared_by, $action]);

                    $responses[] = ["email" => $recipient_email, "message" => "Share email has been sent."];
                } else {
                    $responses[] = ["email" => $recipient_email, "message" => "Share successful but email could not be sent."];
                }
            } else {
                $responses[] = ["email" => $recipient_email, "message" => "Unable to share note."];
            }
        }
    }

    echo json_encode($responses);
    exit;
}

// Get list of notes you have shared
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

// Get list of notes shared with you
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