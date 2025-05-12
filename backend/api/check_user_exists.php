<?php
require "config.php"; // Kết nối tới cơ sở dữ liệu

set_cors_header();
check_login();

// Check for POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? '';

if (!$email) {
    echo json_encode(['error' => 'Email is required']);
    exit;
}

// Check if user exists
$stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
$stmt->execute([$email]);
$count = $stmt->fetchColumn();

echo json_encode(['exists' => $count > 0]);