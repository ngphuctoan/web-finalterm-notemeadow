<?php

require "config.php"; // Database connection
session_start();

set_cors_header();
check_login();

// Kiểm tra phương thức
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed."]);
    exit;
}

try {
    // Kiểm tra xem có file được upload không
    if (!isset($_FILES["image"]) || $_FILES["image"]["error"] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(["message" => "No file uploaded or upload error."]);
        exit;
    }

    $file = $_FILES["image"];
    $allowed_types = ["image/jpeg", "image/png", "image/gif"];
    $max_size = 5 * 1024 * 1024; // 5MB

    // Kiểm tra loại file
    if (!in_array($file["type"], $allowed_types)) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid file type. Only JPG, PNG and GIF are allowed."]);
        exit;
    }

    // Kiểm tra kích thước file
    if ($file["size"] > $max_size) {
        http_response_code(400);
        echo json_encode(["message" => "File size exceeds limit. Maximum size is 5MB."]);
        exit;
    }

    // Tạo tên file mới
    $extension = pathinfo($file["name"], PATHINFO_EXTENSION);
    $new_filename = uniqid() . "." . $extension;
    $upload_dir = "../uploads/images/";

    // Tạo thư mục nếu chưa tồn tại
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $target_path = $upload_dir . $new_filename;

    // Di chuyển file
    if (move_uploaded_file($file["tmp_name"], $target_path)) {
        echo json_encode([
            "message" => "Image uploaded successfully.",
            "image" => $new_filename
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to upload file."]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Error uploading image: " . $e->getMessage()]);
}
