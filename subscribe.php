<?php
session_start();
require 'config/constants.php';
require 'config/database.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Check if email already exists
        $check_query = "SELECT id FROM subscribers WHERE email = ?";
        $stmt = mysqli_prepare($connection, $check_query);
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $_SESSION['subscribe_message'] = 'You are already subscribed!';
            $_SESSION['subscribe_type'] = 'info';
        } else {
            // Insert new subscriber
            $insert_query = "INSERT INTO subscribers (email) VALUES (?)";
            $stmt = mysqli_prepare($connection, $insert_query);
            mysqli_stmt_bind_param($stmt, 's', $email);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['subscribe_message'] = 'Thank you for subscribing!';
                $_SESSION['subscribe_type'] = 'success';
            } else {
                $_SESSION['subscribe_message'] = 'Subscription failed. Please try again.';
                $_SESSION['subscribe_type'] = 'error';
            }
        }
    } else {
        $_SESSION['subscribe_message'] = 'Please enter a valid email address.';
        $_SESSION['subscribe_type'] = 'error';
    }

    // Redirect back to the referring page
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ROOT_URL . 'index.php';
    header('Location: ' . $referer);
    exit();
} else {
    // If not a POST request, redirect to home
    header('Location: ' . ROOT_URL . 'index.php');
    exit();
}
?>
