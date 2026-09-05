<?php
// Include necessary configuration files
require 'config/database.php';

// Start the session to access session variables
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in before proceeding with logout
if (!isset($_SESSION['user-id'])) {
    // If not logged in, redirect to signin page with an error message
    header('Location: ' . ROOT_URL . 'signin.php?error=not_logged_in');
    die();
}

// Get user ID for logging purposes
$user_id = $_SESSION['user-id'];

// Log the logout event to the database for audit purposes
// Check if 'logs' table exists before attempting to log
$result = $connection->query("SHOW TABLES LIKE 'logs'");
if ($result && $result->num_rows > 0) {
    $action = 'logout';
    $timestamp = date('Y-m-d H:i:s');

    $query = "INSERT INTO logs (user-id, action, timestamp) VALUES (?, ?, ?)";
    $stmt = $connection->prepare($query);
    if ($stmt) {
        $stmt->bind_param('iss', $user_id, $action, $timestamp);
        $stmt->execute();
        $stmt->close();
    } else {
        // Handle database error (log to error log or handle gracefully)
        error_log("Database error during logout logging: " . $connection->error);
    }
} else {
    // Logs table does not exist, skip logging but continue with logout
    error_log("Logs table does not exist, skipping logout logging");
}

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to signin page with a success message
header('Location: ' . ROOT_URL . 'signin.php?message=logout_success');
die();
?>
