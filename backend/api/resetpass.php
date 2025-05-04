<?php
require "config.php";
session_start();

// Bật hiển thị lỗi
error_reporting(E_ALL);
ini_set("display_errors", 1);

// 🔥 Thêm header để bật CORS
header("Access-Control-Allow-Origin: http://localhost:1234");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$expired_message = "";
$reset = null;

$token = $_GET["token"] ?? "";
$current_time = date("Y-m-d H:i:s");

// Kiểm tra token
if ($token) {
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ?");
    $stmt->execute([$token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($reset) {
        $expires = $reset["expires"];

        if ($current_time > $expires) {
            http_response_code(400);
            echo json_encode(["message" => "Liên kết đã hết hạn. Vui lòng yêu cầu một liên kết mới."]);
            exit;
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Mã xác thực không hợp lệ. Vui lòng kiểm tra lại."]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đặt Lại Mật Khẩu</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        h2 {
            color: #333;
        }
        form {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: auto;
        }
        label {
            display: block;
            margin-bottom: 10px;
        }
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <h2>Đặt Lại Mật Khẩu</h2>
    <?php if (!isset($reset) || $expired_message || $current_time > ($reset["expires"] ?? "")): ?>
        <p><?php echo htmlspecialchars($expired_message); ?> hoặc liên kết đặt lại mật khẩu đã hết hạn.</p>
    <?php else: ?>
        <form action="reset_password_form.php?token=<?php echo htmlspecialchars($token); ?>" method="POST">
            <label for="new_password">Mật khẩu mới:</label>
            <input type="password" name="new_password" required>
            <button type="submit">Cập nhật mật khẩu</button>
        </form>
    <?php endif; ?>
</body>
</html>