<?php
require_once "config.php";

session_start();

set_cors_header();
check_login();

echo json_encode(["logged_in" => true]);