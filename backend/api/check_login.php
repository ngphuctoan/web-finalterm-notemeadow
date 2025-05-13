<?php
require_once "config.php";

session_start();

set_cors_header();

echo json_encode(["logged_in" => isset($_SESSION["user_id"])]);