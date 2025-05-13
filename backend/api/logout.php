<?php

session_start();

set_cors_header();

session_unset();
session_destroy();
echo json_encode(["message" => "Logout successful."]);
