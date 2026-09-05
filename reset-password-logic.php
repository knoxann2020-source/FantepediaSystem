<?php
session_start();
require 'config/database.php';

if (isset($_POST['submit'])) {
    $token = filter_var($_POST['token'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $new_password = filter_var($_POST['new_password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $confirm_password = filter_var($_POST['confirm_password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (!$new_password || !$confirm_password) {
        $_SESSION['reset-error'] = "Please fill in all fields.";
        header('location: ' . ROOT_URL . 'reset-password.php?token=' . $token);
        die();
    }

    if (strlen($new_password) < 8) {
        $_SESSION['reset-error'] = "Password must be at least 8 characters long.";
        header('location: ' . ROOT_URL . 'reset-password.php?token=' . $token);
        die();
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['reset-error'] = "Passwords do not match.";
        header('location: ' . ROOT_URL . 'reset-password.php?token=' . $token);
        die();
    }

    // Verify token and get user ID
    $token_query = "SELECT user_id FROM password_resets WHERE token = ? AND expires_at > NOW()";
    $stmt = mysqli_prepare($connection, $token_query);
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 1) {
        $reset_data = mysqli_fetch_assoc($result);
        $user_id = $reset_data['user_id'];

        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update user password
        $update_query = "UPDATE users SET password = ? WHERE id = ?";
        $update_stmt = mysqli_prepare($connection, $update_query);
        mysqli_stmt_bind_param($update_stmt, "si", $hashed_password, $user_id);

        if (mysqli_stmt_execute($update_stmt)) {
            // Delete the reset token
            $delete_query = "DELETE FROM password_resets WHERE token = ?";
            $delete_stmt = mysqli_prepare($connection, $delete_query);
            mysqli_stmt_bind_param($delete_stmt, "s", $token);
            mysqli_stmt_execute($delete_stmt);

            $_SESSION['reset-success'] = "Password has been reset successfully. You can now sign in with your new password.";
            header('location: ' . ROOT_URL . 'signin.php');
            die();
        } else {
            $_SESSION['reset-error'] = "Failed to update password. Please try again.";
        }
    } else {
        $_SESSION['reset-error'] = "Invalid or expired reset token.";
    }

    header('location: ' . ROOT_URL . 'reset-password.php?token=' . $token);
    die();
} else {
    header('location: ' . ROOT_URL . 'signin.php');
    die();
}
?>
