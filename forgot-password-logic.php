<?php
session_start();
require 'config/database.php';

if (isset($_POST['submit'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!$email) {
        $_SESSION['forgot-error'] = "Please enter a valid email address";
        header('location: ' . ROOT_URL . 'forgot-password.php');
        die();
    }

    // Check if email exists in users table
    $user_query = "SELECT id, email FROM users WHERE email = ?";
    $stmt = mysqli_prepare($connection, $user_query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        $user_id = $user['id'];

        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Delete any existing reset tokens for this user
        $delete_query = "DELETE FROM password_resets WHERE user_id = ?";
        $delete_stmt = mysqli_prepare($connection, $delete_query);
        mysqli_stmt_bind_param($delete_stmt, "i", $user_id);
        mysqli_stmt_execute($delete_stmt);

        // Insert new reset token
        $insert_query = "INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)";
        $insert_stmt = mysqli_prepare($connection, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, "iss", $user_id, $token, $expires_at);

        if (mysqli_stmt_execute($insert_stmt)) {
            // Send email with reset link
            $reset_link = ROOT_URL . "reset-password.php?token=" . $token;
            $subject = "Password Reset Request - Fantepedia System";
            $message = "Hello,\n\nYou have requested to reset your password. Click the link below to reset your password:\n\n" . $reset_link . "\n\nThis link will expire in 1 hour.\n\nIf you didn't request this, please ignore this email.\n\nBest regards,\nFantepedia System Team";
            $headers = "From: noreply@fantepedia.com";

            if (mail($email, $subject, $message, $headers)) {
                $_SESSION['forgot-success'] = "Password reset link has been sent to your email address.";
            } else {
                $_SESSION['forgot-error'] = "Failed to send reset email. Please try again.";
            }
        } else {
            $_SESSION['forgot-error'] = "Failed to generate reset token. Please try again.";
        }
    } else {
        $_SESSION['forgot-error'] = "No account found with this email address.";
    }

    header('location: ' . ROOT_URL . 'forgot-password.php');
    die();
} else {
    header('location: ' . ROOT_URL . 'forgot-password.php');
    die();
}
?>
