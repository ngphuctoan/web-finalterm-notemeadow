<?php

require "config.php"; // Kết nối tới cơ sở dữ liệu
session_start();

set_cors_header();
check_login();

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


try {
    // **Xử lý tạo ghi chú mới (POST)**
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_GET["action"]) && $_GET["action"] === "create_note") {
        $title = isset($_POST["title"]) ? trim($_POST["title"]) : "";
        $content = isset($_POST["content"]) ? trim($_POST["content"]) : "";
        $is_pinned = isset($_POST["is_pinned"]) ? (int)$_POST["is_pinned"] : 0;
        $category = isset($_POST["category"]) ? trim($_POST["category"]) : null;

        // Nhận tags dưới dạng chuỗi và chuyển đổi thành mảng
        $tags = isset($_POST["tags"]) ? trim($_POST["tags"]) : "";
        $tagsArray = !empty($tags) ? explode(",", $tags) : [];

        $password = isset($_POST["password"]) ? trim($_POST["password"]) : null;

        $imagePaths = []; // Mảng để lưu đường dẫn ảnh

        // Kiểm tra tệp ảnh
        if (isset($_FILES["images"]) && is_array($_FILES["images"]["name"])) {
            $target_dir = "uploads/";

            // Kiểm tra xem thư mục uploads có tồn tại không, nếu không thì tạo
            if (!is_dir($target_dir)) {
                if (!mkdir($target_dir, 0777, true)) {
                    echo json_encode(["message" => "Could not create uploads directory."]);
                    exit;
                }
            }

            foreach ($_FILES["images"]["name"] as $key => $name) {
                if ($_FILES["images"]["error"][$key] === UPLOAD_ERR_OK) {
                    $target_file = $target_dir . basename($name);
                    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                    // Kiểm tra định dạng ảnh
                    $allowed_types = ["jpg", "png", "jpeg", "gif"];
                    if (!in_array($imageFileType, $allowed_types)) {
                        echo json_encode(["message" => "Only JPG, JPEG, PNG, and GIF files are allowed."]);
                        exit;
                    }

                    // Kiểm tra kích thước ảnh (optional)
                    if ($_FILES["images"]["size"][$key] > 5000000) { // 5MB max size
                        echo json_encode(["message" => "Image is too large. Maximum size is 5MB."]);
                        exit;
                    }

                    // Tải ảnh lên
                    if (move_uploaded_file($_FILES["images"]["tmp_name"][$key], $target_file)) {
                        $imagePaths[] = $target_file; // Lưu đường dẫn ảnh
                    } else {
                        echo json_encode(["message" => "An error occurred while uploading the image."]);
                        exit;
                    }
                } else {
                    // Xử lý lỗi tải lên
                    echo json_encode(["message" => "Upload error: " . $_FILES["images"]["error"][$key]]);
                    exit;
                }
            }
        }

        $font_size = "16px";
        $color_note = "gray";

        // Chèn dữ liệu vào database
        $stmt = $pdo->prepare("
            INSERT INTO notes (user_id, title, content, created_at, modified_at, is_pinned, category, tags, password, image, font_size, note_color)
            VALUES (?, ?, ?, NOW(), NOW(), ?, ?, ?, ?, ?, ?, ?)
        ");

        // Chuyển mảng đường dẫn ảnh thành JSON
        $imageJson = json_encode($imagePaths);

        if ($stmt->execute([
            $_SESSION["user_id"],
            $title,
            $content,
            $is_pinned,
            $category,
            implode(",", $tagsArray), // Sử dụng chuỗi tags
            $password,
            $imageJson,
            $font_size,
            $color_note
        ])) {
            // Lấy id của ghi chú vừa tạo
            $note_id = $pdo->lastInsertId();

            // Ghi vào bảng lịch sử
            $historyStmt = $pdo->prepare("
                INSERT INTO note_history (note_id, user_id, action)
                VALUES (?, ?, ?)
            ");
            $historyStmt->execute([$note_id, $_SESSION["user_id"], "Note has been created."]);

            // Thêm các nhãn vào bảng note_tags
            foreach ($tagsArray as $tag) {
                $tag = trim($tag); // Xóa khoảng trắng

                // Kiểm tra xem nhãn đã tồn tại hay chưa
                $stmt = $pdo->prepare("SELECT id FROM tags WHERE name = ? AND user_id = ?");
                $stmt->execute([$tag, $_SESSION["user_id"]]);
                $tag_id = $stmt->fetchColumn();

                if ($tag_id) {
                    // Chèn vào bảng note_tags
                    $stmt = $pdo->prepare("INSERT INTO note_tags (note_id, tag_id) VALUES (?, ?)");
                    $stmt->execute([$note_id, $tag_id]);
                } else {
                    // Nếu nhãn không tồn tại, bạn có thể muốn tạo nhãn mới
                    $stmt = $pdo->prepare("INSERT INTO tags (name, user_id) VALUES (?, ?)");
                    $stmt->execute([$tag, $_SESSION["user_id"]]);
                    $tag_id = $pdo->lastInsertId(); // Lấy id của nhãn mới

                    // Chèn vào bảng note_tags
                    $stmt = $pdo->prepare("INSERT INTO note_tags (note_id, tag_id) VALUES (?, ?)");
                    $stmt->execute([$note_id, $tag_id]);
                }
            }

            http_response_code(201);
            echo json_encode([
                "message" => "Note has been created successfully.",
                "id" => $note_id,
                "images" => $imagePaths
            ]);
        } else {
            error_log("SQL ERROR: " . print_r($stmt->errorInfo(), true));
            http_response_code(500);
            echo json_encode(["message" => "Error saving note."]);
        }
        exit;
    }


    // **Thay đổi mật khẩu ghi chú (PUT)**
    if ($_SERVER["REQUEST_METHOD"] === "PUT" && isset($_GET["action"]) && $_GET["action"] === "change_password") {
        $data = json_decode(file_get_contents("php://input"), true);
        $note_id = $data["note_id"] ?? "";
        $current_password = $data["current_password"] ?? "";
        $new_password = $data["new_password"] ?? "";
        $confirm_password = $data["confirm_password"] ?? "";

        if (!empty($note_id)) {
            if ($new_password !== $confirm_password) {
                echo json_encode(["message" => "New password and confirm password do not match."]);
                exit;
            }

            // Kiểm tra mật khẩu hiện tại
            $stmt = $pdo->prepare("SELECT password FROM notes WHERE id = ? AND user_id = ?");
            $stmt->execute([$note_id, $_SESSION["user_id"]]);
            $note = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($note) {
                if ($note["password"] === $current_password || $current_password === "") {
                    // Cập nhật mật khẩu mới
                    $stmt = $pdo->prepare("UPDATE notes SET password = ? WHERE id = ? AND user_id = ?");
                    $stmt->execute([$new_password, $note_id, $_SESSION["user_id"]]);

                    // Ghi vào bảng lịch sử
                    $historyStmt = $pdo->prepare("
                        INSERT INTO note_history (note_id, user_id, action)
                        VALUES (?, ?, ?)
                    ");
                    $historyStmt->execute([$note_id, $_SESSION["user_id"], "Password has been changed for note ".$note_id]);

                    echo json_encode(["message" => "Password has been changed successfully."]);
                } else {
                    echo json_encode(["message" => "Current password is incorrect."]);
                }
            } else {
                echo json_encode(["message" => "Note not found."]);
            }
        } else {
            echo json_encode(["message" => "Incomplete information."]);
        }
        exit;
    }

    // **Bật bảo vệ bằng mật khẩu (DELETE)**
    if ($_SERVER["REQUEST_METHOD"] === "PUT" && isset($_GET["action"]) && $_GET["action"] === "enable_password") {
        $data = json_decode(file_get_contents("php://input"), true);
        $note_id = $data["note_id"] ?? "";
        $user_id = $_SESSION["user_id"];

        if (!empty($note_id)) {
            $stmt = $pdo->prepare("UPDATE notes SET status_pass = 1 WHERE id = ? AND user_id = ?");
            $stmt->execute([$note_id, $user_id]);

            $historyStmt = $pdo->prepare("
                INSERT INTO note_history (note_id, user_id, action)
                VALUES (?, ?, ?)
            ");
            $historyStmt->execute([$note_id, $user_id, "Password protection has been enabled."]);

            echo json_encode(["message" => "Password protection has been enabled."]);
        } else {
            echo json_encode(["message" => "Incomplete information."]);
        }
        exit;
    }

    // **Bật bảo vệ bằng mật khẩu (DELETE)**
    if ($_SERVER["REQUEST_METHOD"] === "PUT" && isset($_GET["action"]) && $_GET["action"] === "disable_password") {
        $data = json_decode(file_get_contents("php://input"), true);
        $note_id = $data["note_id"] ?? "";
        $user_id = $_SESSION["user_id"];

        if (!empty($note_id)) {
            $stmt = $pdo->prepare("UPDATE notes SET status_pass = 0 WHERE id = ? AND user_id = ?");
            $stmt->execute([$note_id, $user_id]);

            $historyStmt = $pdo->prepare("
                INSERT INTO note_history (note_id, user_id, action)
                VALUES (?, ?, ?)
            ");
            $historyStmt->execute([$note_id, $user_id, "Password protection has been disabled."]);

            echo json_encode(["message" => "Password protection has been disabled."]);
        } else {
            echo json_encode(["message" => "Incomplete information."]);
        }
        exit;
    }

    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"])) {
        $action = $_GET["action"];

        // **Xem ghi chú cá nhân (GET)**
        if ($action === "view_note") {
            // $note_id_encode = $_GET["note_id"];
            // $note_id = decodeNumber($note_id_encode,$key);

            $note_id = $_GET["note_id"];
            $input_password = $_GET["password"] ?? null;
            $user_id = $_SESSION["user_id"] ;

            if (empty($note_id)) {
                echo json_encode(["error" => true, "message" => "❌ Missing note_id information."]);
                exit;
            }

            // Truy vấn ghi chú của người dùng
            $stmt = $pdo->prepare("SELECT * FROM notes WHERE id = ? AND user_id = ? ORDER BY is_pinned DESC, GREATEST(modified_at, created_at) DESC");
            $stmt->execute([$note_id, $user_id]);
            $note = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($note) {
                // Kiểm tra mật khẩu (nếu có)
                if (empty($note["password"]) || $note["password"] === $input_password) {
                    echo json_encode(["success" => true, "message" => "✅ Access successful.", "note" => $note]);
                } else {
                    echo json_encode(["error" => true, "message" => "❌ Incorrect password."]);
                }

            } else {
                echo json_encode(["error" => true, "message" => "❌ Note does not exist or you don't have access."]);
            }
            exit;
        }

        // **Xem ghi chú được chia sẻ (GET)**
        if ($action === "view_shared_note") {
            $note_id = $_GET["note_id"] ?? "";
            $input_password = $_GET["password"] ?? null;

            if (empty($note_id)) {
                echo json_encode(["error" => true, "message" => "❌ Missing note_id information."]);
                exit;
            }

            // Truy vấn quyền truy cập từ bảng `shared_notes`
            $stmt = $pdo->prepare("SELECT * FROM shared_notes WHERE note_id = ? AND password = ?");
            $stmt->execute([$note_id, $input_password]);
            $shared_note = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($shared_note) {
                // Lấy thông tin ghi chú từ bảng `notes`
                $stmt = $pdo->prepare("SELECT * FROM notes WHERE id = ?");
                $stmt->execute([$note_id]);
                $note = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($note) {
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
                        "permission" => $shared_note["permission"],
                        "image" => json_decode($note["image"], true), // Chuyển JSON sang mảng PHP
                        "can_edit" => ($shared_note["permission"] === "edit")
                    ];

                    echo json_encode(["success" => true, "message" => "✅ Access successful.", "note" => $note_data]);
                } else {
                    echo json_encode(["error" => true, "message" => "❌ Note does not exist."]);
                }
            } else {
                echo json_encode(["error" => true, "message" => "❌ Note does not exist or you don't have access."]);
            }
            exit;
        }

        if ($action === "view_notes") {
            if (!empty($_SESSION["user_id"])) {
                // Truy vấn tất cả ghi chú của user
                $stmt = $pdo->prepare("
                    SELECT * FROM notes 
                    WHERE user_id = ? 
                    ORDER BY is_pinned DESC, GREATEST(modified_at, created_at) DESC
                ");
                $stmt->execute([$_SESSION["user_id"]]);
                $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Gắn tag vào từng note
                foreach ($notes as &$note) {
                    $note_id = $note["id"];
                    $tagStmt = $pdo->prepare("
                        SELECT t.id, t.name 
                        FROM note_tags nt
                        JOIN tags t ON nt.tag_id = t.id
                        WHERE nt.note_id = ?
                    ");
                    $tagStmt->execute([$note_id]);
                    $note["tags"] = $tagStmt->fetchAll(PDO::FETCH_ASSOC); // Gắn tags thành mảng vào note
                }

                echo json_encode(["success" => true, "notes" => $notes]);
            } else {
                echo json_encode(["error" => true, "message" => "❌ User not logged in."]);
            }
            exit;
        }


        // **Xem tất cả ghi chú cá nhân (GET)**
        if ($action === "get_note_history") {
            // Kiểm tra xem người dùng đã đăng nhập chưa
            if (isset($_SESSION["user_id"])) {
                $user_id = $_SESSION["user_id"];

                // Truy vấn lấy lịch sử ghi chú của người dùng kèm theo tên hiển thị
                $stmt = $pdo->prepare("
                    SELECT nh.id, nh.note_id, nh.user_id, nh.action, nh.timestamp, u.display_name 
                    FROM note_history nh 
                    JOIN users u ON nh.user_id = u.id 
                    WHERE nh.user_id = ? 
                    ORDER BY nh.timestamp DESC
                ");

                $stmt->execute([$user_id]);

                $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Kiểm tra xem có dữ liệu không
                if ($history) {
                    echo json_encode(["history" => $history]);
                } else {
                    echo json_encode(["message" => "No history data available."]);
                }
            } else {
                echo json_encode(["message" => "User not logged in."]);
            }
            exit;
        }

        if ($action === "get_note_history_by_id") {
            // Kiểm tra xem người dùng đã đăng nhập chưa
            if (isset($_SESSION["user_id"])) {
                $user_id = $_SESSION["user_id"];
                $note_id = $_GET["note_id"];
                // Truy vấn lấy lịch sử ghi chú theo ID ghi chú
                $stmt = $pdo->prepare("
                    SELECT nh.id, nh.note_id, nh.user_id, nh.action, nh.timestamp, u.display_name 
                    FROM note_history nh 
                    JOIN users u ON nh.user_id = u.id 
                    WHERE nh.note_id = ? 
                    ORDER BY nh.timestamp DESC
                ");

                $stmt->execute([$note_id]);

                $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Kiểm tra xem có dữ liệu không
                if ($history) {
                    echo json_encode(["history" => $history]);
                } else {
                    echo json_encode(["message" => "No history data available for this note."]);
                }
            } else {
                echo json_encode(["message" => "User not logged in."]);
            }
            exit;
        }


    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_GET["action"]) && $_GET["action"] === "update_tags") {
        $noteId = $_POST["note_id"] ?? null;
        $tagIds = $_POST["tag_ids"] ?? [];

        if (!$noteId || !is_array($tagIds)) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid input"]);
            exit;
        }

        // Example user_id from session
        $userId = $_SESSION["user_id"];

        // First, check if the note belongs to this user
        $stmt = $pdo->prepare("
            SELECT id FROM notes
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$noteId, $userId]);
        if (!$stmt->fetch()) {
            http_response_code(403);
            echo json_encode(["error" => "Forbidden"]);
            exit;
        }

        // Remove old tags
        $stmt = $pdo->prepare("DELETE FROM note_tags WHERE note_id = ?");
        $stmt->execute([$noteId]);

        // Add new ones
        $stmt = $pdo->prepare("INSERT INTO note_tags (note_id, tag_id) VALUES (?, ?)");
        foreach ($tagIds as $tagId) {
            $stmt->execute([$noteId, $tagId]);
        }

        echo json_encode(["success" => true]);
        exit;
    }

    // Nếu không có action hợp lệ
    echo json_encode(["error" => true, "message" => "⚠ Invalid action."]);
    exit;


} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Error saving data: " . htmlspecialchars($e->getMessage())]);
}
