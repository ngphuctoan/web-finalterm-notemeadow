<?php

require "config.php"; // Kết nối tới cơ sở dữ liệu

session_start(); // Khởi tạo session ở đầu tệp

// 🔥 Thêm header để bật CORS
header("Access-Control-Allow-Origin: http://localhost:1234");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Trả về JSON
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true); // Nhận dữ liệu JSON

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Kiểm tra xem email và password có được cung cấp không
    if (empty($data["email"]) || empty($data["password"])) {
        echo json_encode(["message" => "Please provide email and password."]);
        exit;
    }

    $email = $data["email"];
    $password = $data["password"];

    // Kiểm tra xem email có hợp lệ không
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["logged_in" => false, "message" => "Invalid email format."]);
        exit;
    }

    // Kiểm tra độ dài của mật khẩu (tối thiểu 6 ký tự)
    if (strlen($password) < 6) {
        echo json_encode(["logged_in" => false, "message" => "Password must be at least 6 characters long."]);
        exit;
    }

    // Kiểm tra thông tin đăng nhập
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Kiểm tra nếu người dùng tồn tại và mật khẩu đúng
        if ($user && password_verify($password, $user["password"])) {
            // Lưu thông tin người dùng vào session nếu tài khoản đã được kích hoạt
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_email"] = $user["email"]; // Lưu email vào session
            $_SESSION["is_active"] = $user["is_active"]; // Lưu email vào session

            // Kiểm tra trạng thái kích hoạt
            if ($user["is_active"] == 0) {
                echo json_encode(["logged_in" => true, "message" => "Account is not activated. Please check your email to activate."]);
                exit;
            }

            echo json_encode(["logged_in" => true, "message" => "Login successful."]);
        } else {
            echo json_encode(["logged_in" => false, "message" => "Invalid email or password."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["logged_in" => false, "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["logged_in" => false, "message" => "Invalid request."]);
}
