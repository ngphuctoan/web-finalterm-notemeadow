<?php

require "config.php"; // Kết nối tới cơ sở dữ liệu
session_start();


// 🔥 Thêm header để bật CORS
header("Access-Control-Allow-Origin: http://localhost:1234");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Trả về JSON
header("Content-Type: application/json");


//Kiểm tra đăng nhập
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["message" => "Chưa đăng nhập."]);
    exit;
}


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
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
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
                    echo json_encode(["message" => "Không thể tạo thư mục uploads."]);
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
                        echo json_encode(["message" => "Chỉ cho phép tải lên các định dạng JPG, JPEG, PNG, GIF."]);
                        exit;
                    }

                    // Kiểm tra kích thước ảnh (optional)
                    if ($_FILES["images"]["size"][$key] > 5000000) { // 5MB max size
                        echo json_encode(["message" => "Ảnh quá lớn. Kích thước tối đa là 5MB."]);
                        exit;
                    }

                    // Tải ảnh lên
                    if (move_uploaded_file($_FILES["images"]["tmp_name"][$key], $target_file)) {
                        $imagePaths[] = $target_file; // Lưu đường dẫn ảnh
                    } else {
                        echo json_encode(["message" => "Có lỗi khi tải ảnh lên."]);
                        exit;
                    }
                } else {
                    // Xử lý lỗi tải lên
                    echo json_encode(["message" => "Lỗi tải ảnh: " . $_FILES["images"]["error"][$key]]);
                    exit;
                }
            }
        }

        $font_size = "16px";
        $color_note = "#ffffff";

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
            $historyStmt->execute([$note_id, $_SESSION["user_id"], "Đã tạo mới ghi chú."]);

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
                "message" => "Ghi chú đã được tạo thành công.",
                "images" => $imagePaths
            ]);
        } else {
            error_log("LỖI SQL: " . print_r($stmt->errorInfo(), true));
            http_response_code(500);
            echo json_encode(["message" => "Lỗi khi lưu ghi chú."]);
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

        if (!empty($note_id) && !empty($new_password) && !empty($confirm_password)) {
            if ($new_password !== $confirm_password) {
                echo json_encode(["message" => "Mật khẩu mới và xác nhận mật khẩu không khớp."]);
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
                    $historyStmt->execute([$note_id, $_SESSION["user_id"], "Đã thay đổi mật khẩu ghi chú ".$note_id]);

                    echo json_encode(["message" => "Mật khẩu đã được thay đổi."]);
                } else {
                    echo json_encode(["message" => "Mật khẩu hiện tại không đúng."]);
                }
            } else {
                echo json_encode(["message" => "Ghi chú không tìm thấy."]);
            }
        } else {
            echo json_encode(["message" => "Thông tin không đầy đủ."]);
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
            // $stmt->execute([$note_id, $_SESSION["user_id"]]);
            $stmt->execute([$note_id,  $user_id ]);

            $historyStmt = $pdo->prepare("
            INSERT INTO note_history (note_id, user_id, action)
            VALUES (?, ?, ?)
            ");
            // $historyStmt->execute([$note_id, $_SESSION["user_id"], "Bảo vệ bằng mật khẩu đã được tắt."]);
            $historyStmt->execute([$note_id,  $user_id , "Bảo vệ bằng mật khẩu đã được kích hoạt."]);

            echo json_encode(["message" => "Bảo vệ bằng mật khẩu đã được kích hoạt."]);
        } else {
            echo json_encode(["message" => "Thông tin không đầy đủ."]);
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
            // $stmt->execute([$note_id, $_SESSION["user_id"]]);
            $stmt->execute([$note_id,  $user_id ]);

            $historyStmt = $pdo->prepare("
            INSERT INTO note_history (note_id, user_id, action)
            VALUES (?, ?, ?)
            ");
            // $historyStmt->execute([$note_id, $_SESSION["user_id"], "Bảo vệ bằng mật khẩu đã được tắt."]);
            $historyStmt->execute([$note_id,  $user_id , "Bảo vệ bằng mật khẩu đã bị vô hiệu hóa."]);

            echo json_encode(["message" => "Bảo vệ bằng mật khẩu đã bị vô hiệu hóa."]);
        } else {
            echo json_encode(["message" => "Thông tin không đầy đủ."]);
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
                echo json_encode(["error" => true, "message" => "❌ Thiếu thông tin note_id."]);
                exit;
            }

            // Truy vấn ghi chú của người dùng
            $stmt = $pdo->prepare("SELECT * FROM notes WHERE id = ? AND user_id = ? ORDER BY is_pinned DESC, GREATEST(modified_at, created_at) DESC");
            $stmt->execute([$note_id, $user_id]);
            $note = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($note) {
                // Kiểm tra mật khẩu (nếu có)
                if (empty($note["password"]) || $note["password"] === $input_password) {
                    echo json_encode(["success" => true, "message" => "✅ Truy cập thành công.", "note" => $note]);
                } else {
                    echo json_encode(["error" => true, "message" => "❌ Mật khẩu không đúng."]);
                }

            } else {
                echo json_encode(["error" => true, "message" => "❌ Ghi chú không tồn tại hoặc không thuộc quyền truy cập."]);
            }
            exit;
        }

        // **Xem ghi chú được chia sẻ (GET)**
        if ($action === "view_shared_note") {
            $note_id = $_GET["note_id"] ?? "";
            $input_password = $_GET["password"] ?? null;

            if (empty($note_id)) {
                echo json_encode(["error" => true, "message" => "❌ Thiếu thông tin note_id."]);
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

                    echo json_encode(["success" => true, "message" => "✅ Truy cập thành công.", "note" => $note_data]);
                } else {
                    echo json_encode(["error" => true, "message" => "❌ Ghi chú không tồn tại."]);
                }
            } else {
                echo json_encode(["error" => true, "message" => "❌ Ghi chú không tồn tại hoặc không thuộc quyền truy cập."]);
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
                echo json_encode(["error" => true, "message" => "❌ Người dùng chưa đăng nhập."]);
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
                    echo json_encode(["message" => "Không có dữ liệu lịch sử."]);
                }
            } else {
                echo json_encode(["message" => "Người dùng chưa đăng nhập."]);
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
                    echo json_encode(["message" => "Không có dữ liệu lịch sử cho ghi chú này."]);
                }
            } else {
                echo json_encode(["message" => "Người dùng chưa đăng nhập."]);
            }
            exit;
        }


    }

    // Nếu không có action hợp lệ
    echo json_encode(["error" => true, "message" => "⚠ Hành động không hợp lệ."]);
    exit;


} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Lỗi khi lưu dữ liệu: " . htmlspecialchars($e->getMessage())]);
}
