<?php
require 'config.php'; // Database connection
session_start();

set_cors_header();
check_login();

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