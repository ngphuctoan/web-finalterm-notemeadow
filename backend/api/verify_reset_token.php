<?php
// Kiểm tra phương thức
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed."]);
    exit;
}

try {
    $data = json_decode(file_get_contents("php://input"), true);
    $token = $data["token"] ?? "";

    // Kiểm tra token
    if (empty($token)) {
        http_response_code(400);
        echo json_encode(["message" => "Token is required."]);
        exit;
    }

    // Kiểm tra token trong database
    $stmt = $pdo->prepare("SELECT id, email, reset_token_expires FROM users WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(["message" => "Invalid token."]);
        exit;
    }

    // Kiểm tra token hết hạn
    if (strtotime($user["reset_token_expires"]) < time()) {
        http_response_code(400);
        echo json_encode(["message" => "Token has expired."]);
        exit;
    }

    echo json_encode([
        "message" => "Token is valid.",
        "email" => $user["email"]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Error verifying token: " . $e->getMessage()]);
} 