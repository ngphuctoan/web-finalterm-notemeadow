<?php

require "config.php"; // Database connection
session_start();

// 🔥 CORS headers
header("Access-Control-Allow-Origin: http://localhost:1234");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: *");
header("Access-Control-Allow-Headers: Content-Type");

header("Content-Type: application/json");

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    echo json_encode(["message" => "Not logged in"]);
    exit;
}

$user_id = $_SESSION["user_id"];

// Check if file is uploaded
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["image"])) {
    $target_dir = "uploads/";

    // Create the uploads directory if it doesn"t exist
    if (!is_dir($target_dir)) {
        if (!mkdir($target_dir, 0777, true)) {
            echo json_encode(["message" => "Unable to create uploads directory."]);
            exit;
        }
    }

    // Generate a unique filename to avoid collisions (e.g., using user ID + timestamp)
    $file_name = uniqid($user_id . "_", true) . "." . pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
    $target_file = $target_dir . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Allowed image formats
    $allowed_types = ["jpg", "jpeg", "png", "gif"];
    if (!in_array($imageFileType, $allowed_types)) {
        echo json_encode(["message" => "Only JPG, JPEG, PNG, and GIF files are allowed."]);
        exit;
    }

    // Attempt to move the uploaded file
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        echo json_encode(["message" => "Image uploaded successfully", "file_path" => $target_file]);
    } else {
        echo json_encode(["message" => "Error uploading image."]);
    }
} else {
    echo json_encode(["message" => "Invalid request or no image file found."]);
}
