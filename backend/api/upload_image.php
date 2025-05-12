<?php

require_once "config.php"; // Database connection
session_start();

set_cors_header();
check_login();

$user_id = $_SESSION["user_id"];

// Check if file is uploaded
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["image"])) {
    $target_dir = __DIR__ . "/../uploads/images/";
    $public_path = "/uploads/images/";

    // Create the uploads/images directory if it doesn't exist
    if (!is_dir($target_dir)) {
        if (!mkdir($target_dir, 0777, true)) {
            echo json_encode(["message" => "Unable to create upload directory."]);
            exit;
        }
    }

    // Generate a unique filename with original extension
    $extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
    $filename = uniqid() . "." . $extension;
    $target_file = $target_dir . $filename;

    // Allowed image formats
    $allowed_types = ["jpg", "jpeg", "png", "gif"];
    if (!in_array(strtolower($extension), $allowed_types)) {
        echo json_encode(["message" => "Only JPG, JPEG, PNG, and GIF files are allowed."]);
        exit;
    }

    // Move file
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        echo json_encode([
            "message" => "Image uploaded successfully.",
            "file_path" => $public_path . $filename
        ]);
    } else {
        echo json_encode(["message" => "Error uploading image."]);
    }
} else {
    echo json_encode(["message" => "Invalid request or no image file found."]);
}