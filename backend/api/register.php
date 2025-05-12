<?php

require "config.php";
require "send_email.php"; // Include the email sending function

set_cors_header();

function sendActivationEmail($to, $user_name, $activation_token)
{
    $subject = "Verify your Note account";
    $activation_link = "http://" . $_SERVER["HTTP_HOST"] . "/api/activate_account.php?token=" . $activation_token;

    // Using heredoc for better readability
    $body = <<<EOD
<!DOCTYPE html>
<html>
<head>
    <title>Email Confirmation</title>
</head>
<body style="margin-top:20px;">
    <table class="body-wrap" style="font-family: "Helvetica Neue",Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; background-color: #f6f6f6; margin: 0;" bgcolor="#f6f6f6">
        <tbody>
            <tr>
                <td valign="top"></td>
                <td class="container" width="600" valign="top">
                    <div class="content" style="padding: 20px;">
                        <table class="main" width="100%" cellpadding="0" cellspacing="0" style="border-radius: 3px; background-color: #fff; margin: 0; border: 1px solid #e9e9e9;" bgcolor="#fff">
                            <tbody>
                                <tr>
                                    <td class="" style="font-size: 16px; vertical-align: top; color: #fff; font-weight: 500; text-align: center; background-color: #38414a; padding: 20px;" align="center" bgcolor="#71b6f9" valign="top">
                                        <a href="#" style="font-size:32px;color:#fff;text-decoration: none;">Hi there!</a> <br>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="content-wrap" style="padding: 20px;" valign="top">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tbody>
                                                <tr>
                                                    <td class="content-block" style="padding: 0 0 20px;" valign="top">
                                                       Thank you for creating an Note account. To continue setting up your workspace, please verify your email by clicking the link below:
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="content-block" style="text-align: center;" valign="top">
                                                        <a href="$activation_link" class="btn-primary" style="font-size: 14px; color: #FFF; text-decoration: none; line-height: 2em; font-weight: bold; border-radius: 5px; background-color: #D10024; padding: 8px 16px; display: inline-block;">Verify my email address</a>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="content-block" style="padding: 0 0 20px;" valign="top">
                                                        This link will verify your email address, and then you'll officially be a part of the Note Website community.
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="content-block" style="padding: 0 0 20px;" valign="top">
                                                        See you there!
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="content-block" style="padding: 0 0 20px;" valign="top">
                                                        Best regards, the Note Website team.
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </td>
                <td valign="top"></td>
            </tr>
        </tbody>
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
