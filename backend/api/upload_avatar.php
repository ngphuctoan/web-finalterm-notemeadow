<?php
require 'config.php'; // Database connection
session_start();

// 🔥 CORS headers
header("Access-Control-Allow-Origin: http://localhost:1234");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if image path is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['image_path'])) {
    $image_path = $_POST['image_path'];

    // Ensure the image path is valid
    if (empty($image_path) || !file_exists($image_path)) {
        echo json_encode(['message' => 'Invalid image path.']);
        exit;
    }

    // Update the user's avatar in the database
    $stmt = $pdo->prepare("UPDATE users SET image = ? WHERE id = ?");
    if ($stmt->execute([$image_path, $user_id])) {
        echo json_encode(['message' => 'Avatar updated successfully.']);
    } else {
        echo json_encode(['message' => 'Failed to update avatar.']);
    }
} else {
    echo json_encode(['message' => 'No image path provided.']);
}
?>