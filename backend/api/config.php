<?php

$host = "mysql";
$db = "notemeadow";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

function set_cors_header() {
    header("Access-Control-Allow-Origin: $_ENV[CLIENT_URL]");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");

    header("Content-Type: application/json");

    if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
        http_response_code(200);
        exit;
    }
}

function check_login() {
    if (!isset($_SESSION["user_id"])) {
        http_response_code(401);
        echo json_encode(["message" => "Not logged in."]);
        exit;
    }
}