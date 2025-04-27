<?php
require 'config.php'; // Kết nối tới cơ sở dữ liệu
session_start();

// 🔥 Thêm header để bật CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Trả về JSON
header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['message' => 'Chưa đăng nhập.']);
    exit;
}

try {
    // **1. Xem danh sách nhãn (GET)**
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'list_tags') {
        $stmt = $pdo->prepare("SELECT * FROM tags WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($tags);
        exit;
    }

    // **2. Thêm nhãn mới (POST)**
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'add_tag') {
        $data = json_decode(file_get_contents("php://input"), true);
        $tag_name = $data['name'] ?? '';

        if (!empty($tag_name)) {
            $stmt = $pdo->prepare("INSERT INTO tags (name, user_id) VALUES (?, ?)");
            $stmt->execute([$tag_name, $_SESSION['user_id']]);
            echo json_encode(['message' => 'Nhãn đã được thêm.']);
        } else {
            echo json_encode(['message' => 'Tên nhãn không hợp lệ.']);
        }
        exit;
    }

    // **Đổi tên nhãn (PUT)**
    if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['action']) && $_GET['action'] === 'rename_tag') {
        $data = json_decode(file_get_contents("php://input"), true);
        $tag_id = $data['tag_id'] ?? '';
        $old_name = $data['old_name'] ?? '';  // Tên nhãn cần đổi
        $new_name = $data['new_name'] ?? '';  // Tên nhãn mới
    
        if (!empty($tag_id) && !empty($old_name) && !empty($new_name)) {
            // Kiểm tra xem tên nhãn mới có chứa dấu phẩy hay không
            if (strpos($new_name, ',') !== false) {
                echo json_encode(['message' => 'Tên nhãn mới không được chứa dấu phẩy.']);
                exit;
            }

            // Kiểm tra xem tên nhãn mới có ký tự gạch dưới hay không
            if (preg_match('/[^a-zA-Z0-9_]/', $new_name)) {
                echo json_encode(['message' => 'Tên nhãn chỉ được chứa các ký tự chữ cái, số và dấu gạch dưới (_).']);
                exit;
            }

            // Bắt đầu transaction để đảm bảo tính toàn vẹn dữ liệu
            $pdo->beginTransaction();
    
            try {
                // 1. Lấy nhãn cũ từ bảng tags trước khi cập nhật
                $stmt = $pdo->prepare("SELECT name FROM tags WHERE id = ? AND user_id = ?");
                $stmt->execute([$tag_id, $_SESSION['user_id']]);
                $current_name = $stmt->fetchColumn();
    
                if (!$current_name) {
                    throw new Exception("Không tìm thấy nhãn với ID đã cung cấp.");
                }

                // Kiểm tra nếu nhãn cũ là đúng với nhãn cần thay đổi
                if ($current_name !== $old_name) {
                    throw new Exception("Tên nhãn cũ không khớp với nhãn trong bảng tags.");
                }

                // 2. Cập nhật tên nhãn trong bảng tags
                $stmt = $pdo->prepare("UPDATE tags SET name = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$new_name, $tag_id, $_SESSION['user_id']]);

                // 3. Cập nhật nhãn trong bảng notes
                // Thay thế nhãn cũ bằng nhãn mới trong chuỗi tags trong bảng notes
                $stmt = $pdo->prepare("
                    UPDATE notes 
                    SET tags = REPLACE(tags, ?, ?)
                    WHERE FIND_IN_SET(? , tags) > 0 
                    AND user_id = ?
                ");
                $stmt->execute([$old_name, $new_name, $old_name, $_SESSION['user_id']]);
    
                // Commit transaction nếu không có lỗi
                $pdo->commit();
                echo json_encode(['message' => 'Nhãn đã được đổi tên thành công trong cả hai bảng.']);
            } catch (Exception $e) {
                // Rollback nếu có lỗi
                $pdo->rollBack();
                echo json_encode(['message' => 'Lỗi khi cập nhật nhãn.', 'error' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['message' => 'Thông tin không đầy đủ.']);
        }
        exit;
    }
    
    // **4. Xóa nhãn (DELETE)**
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['action']) && $_GET['action'] === 'delete_tag') {
        $data = json_decode(file_get_contents("php://input"), true);
        $tag_id = $data['tag_id'] ?? '';

        if (!empty($tag_id)) {
            $stmt = $pdo->prepare("DELETE FROM tags WHERE id = ? AND user_id = ?");
            $stmt->execute([$tag_id, $_SESSION['user_id']]);
            echo json_encode(['message' => 'Nhãn đã được xóa.']);
        } else {
            echo json_encode(['message' => 'Thông tin không đầy đủ.']);
        }
        exit;
    }

    // **5. Lọc ghi chú theo nhãn (GET)**
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'filter_notes_by_tag') {
    $tag_id = $_GET['tag_id'] ?? '';
    
    if (!empty($tag_id)) {
        // Lấy ghi chú theo nhãn
        $stmt = $pdo->prepare("SELECT notes.* FROM notes 
            JOIN note_tags ON notes.id = note_tags.note_id 
            WHERE note_tags.tag_id = ? AND notes.user_id = ?");
        $stmt->execute([$tag_id, $_SESSION['user_id']]);

        // Kiểm tra xem có ghi chú nào được tìm thấy không
        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($notes) {
            echo json_encode($notes);
        } else {
            echo json_encode(['message' => 'Không tìm thấy ghi chú nào liên quan đến nhãn này.']);
        }
    } else {
        echo json_encode(['message' => 'Thông tin không đầy đủ.']);
    }
    exit;
}

    // **5. Lọc ghi chú theo nhãn (GET)**
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'filter_notes_by_tag_name') {
        $tag_name = isset($_GET['tag_name']) ? trim($_GET['tag_name']) : '';
        // Thêm ký tự % để sử dụng với LIKE
        $tag_name = '%' . $tag_name . '%';
                
        if (!empty($tag_name)) {
            // Lấy ghi chú theo nhãn
            $stmt = $pdo->prepare("SELECT  *  FROM notes WHERE tags LIKE ? AND user_id = ?");
            $stmt->execute([$tag_name, $_SESSION['user_id']]);
    
            // Kiểm tra xem có ghi chú nào được tìm thấy không
            $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($notes) {
                echo json_encode($notes);
            } else {
                echo json_encode(['message' => 'Không tìm thấy ghi chú nào liên quan đến nhãn này.']);
            }
        } else {
            echo json_encode(['message' => 'Thông tin không đầy đủ.']);
        }
        exit;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Lỗi khi lưu dữ liệu: ' . htmlspecialchars($e->getMessage())]);
}
?>