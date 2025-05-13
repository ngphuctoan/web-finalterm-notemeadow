<?php

require_once "config.php";
require "send_email.php"; // Include the email sending function

set_cors_header();

function sendActivationEmail($to, $user_name, $activation_token)
{
    $subject = "Verify your Note account";
    $activation_link = "http://" . $_SERVER["HTTP_HOST"] . "/api/activate_account.php?token=" . $activation_token;

    // Using heredoc for better readability
    $body = <<<EOD
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Email Confirmation</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600&display=swap" rel="stylesheet" />
</head>
<body style="margin: 0; padding: 0; background-color: #f9fafb; font-family: 'Plus Jakarta Sans', sans-serif; color: #111827;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f9fafb; padding: 40px 0;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden;">
          <!-- Header -->
          <tr>
            <td align="center" style="background-color: #d89614; padding: 24px;">
              <h1 style="color: white; font-size: 28px; margin: 0;">Confirm Your Email</h1>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding: 30px;">
              <p style="font-size: 16px; line-height: 1.6; margin-bottom: 24px;">
                Thank you for signing up for <strong>notemeadow</strong>! You're just one step away from accessing your notes.
              </p>

              <p style="font-size: 16px; line-height: 1.6; margin-bottom: 24px;">
                Please confirm your email address by clicking the button below:
              </p>

              <div style="text-align: center; margin: 32px 0;">
                <a href="$activation_link" style="background-color: #d89614; color: #ffffff; padding: 12px 24px; border-radius: 5px; text-decoration: none; font-weight: 600;">
                  Verify My Email
                </a>
              </div>

              <p style="font-size: 14px; color: #6b7280; line-height: 1.6; margin-bottom: 20px;">
                If the button doesn't work, copy and paste this link into your browser:
                <br>
                <a href="$activation_link" style="color: #00BFFF; word-break: break-all;">$activation_link</a>
              </p>

              <p style="font-size: 14px; color: #6b7280;">
                Once verified, you'll officially become part of the notemeadow community.
              </p>

              <p style="margin-top: 32px; font-size: 14px; color: #6b7280;">
                See you there!<br>
                <strong>The notemeadow Team</strong>
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td align="center" style="padding: 20px; font-size: 12px; color: #9ca3af;">
              &copy; 2025 notemeadow. All rights reserved.
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
EOD;

    return sendEmail($to, $subject, $body);
}

$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $data["email"] ?? "";
    $display_name = $data["display_name"] ?? "";
    $password = $data["password"] ?? "";
    $password_confirmation = $data["password_confirmation"] ?? "";

    // Check if email is empty
    if (empty($email)) {
        echo json_encode(["message" => "Email cannot be empty."]);
        exit;
    } elseif (empty($display_name)) {
        echo json_encode(["message" => "Display name cannot be empty."]);
        exit;
    }

    // Check if email is valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["message" => "Invalid email format."]);
        exit;
    }

    // Check if password is empty
    if (empty($password)) {
        echo json_encode(["message" => "Password cannot be empty."]);
        exit;
    }

    // Check if password is at least 8 characters
    if (strlen($password) < 8) {
        echo json_encode(["message" => "Password must be at least 8 characters long."]);
        exit;
    }

    // Check if password and confirmation match
    if ($password !== $password_confirmation) {
        echo json_encode(["message" => "Password and confirmation do not match."]);
        exit;
    }

    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(["message" => "Email is already in use."]);
            exit;
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $activation_token = bin2hex(random_bytes(16));

        // Insert user data into database
        $stmt = $pdo->prepare("INSERT INTO users (email, display_name, password, activation_token) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$email, $display_name, $hashed_password, $activation_token])) {
            // Send activation email
            if (!sendActivationEmail($email, $display_name, $activation_token)) {
                echo json_encode(["message" => "Could not send activation email."]);
                exit;
            }

            // Auto login
            session_start();
            $_SESSION["user_id"] = $pdo->lastInsertId();
            $_SESSION["user_email"] = $email; // Save email in session

            echo json_encode(["message" => "Registration successful, please check your email to activate your account."]);
        } else {
            echo json_encode(["message" => "Error creating account."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["message" => "Database error: " . $e->getMessage()]);
    } catch (Exception $e) {
        echo json_encode(["message" => "System error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["message" => "Invalid request."]);
}
