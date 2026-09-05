<?php
require 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($connection, $_POST['name']);
    $email = mysqli_real_escape_string($connection, $_POST['email']);
    $message = mysqli_real_escape_string($connection, $_POST['message']);
    $date_time = date('Y-m-d H:i:s');

    // Generate dynamic avatar based on name (first letter)
    $first_letter = strtoupper(substr($name, 0, 1));
    $avatar = "https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=random&color=fff&size=100";

    $query = "INSERT INTO testimonies (name, email, message, date_time, avatar) VALUES ('$name', '$email', '$message', '$date_time', '$avatar')";
    if (mysqli_query($connection, $query)) {
        $_SESSION['testimony_success'] = 'Thank you for your feedback!';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    } else {
        $_SESSION['testimony_error'] = 'Error submitting feedback. Please try again.';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
}
?>
