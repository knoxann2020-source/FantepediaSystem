<?php
require 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location: ' . ROOT_URL . 'signin.php');
    die();
}

// Check if form was submitted
if (isset($_POST['submit'])) {
    // Get form data
    $title = mysqli_real_escape_string($connection, $_POST['title']);
    $category_id = filter_var($_POST['category_id'], FILTER_SANITIZE_NUMBER_INT);
    $content = mysqli_real_escape_string($connection, $_POST['content']);
    $excerpt = mysqli_real_escape_string($connection, $_POST['excerpt']);
    $contact_info = mysqli_real_escape_string($connection, $_POST['contact_info']);
    $user_id = $_SESSION['user_id'];

    // Validate required fields
    if (empty($title) || empty($category_id) || empty($content)) {
        $_SESSION['contribute'] = 'All required fields must be filled.';
        $_SESSION['contribute-data'] = $_POST;
        header('location: ' . ROOT_URL . 'contribute.php');
        die();
    }

    // Handle image uploads
    $uploaded_images = [];
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $target_dir = "images/contributions/";

        // Create directory if it doesn't exist
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if (!empty($tmp_name)) {
                $file_name = time() . '_' . basename($_FILES['images']['name'][$key]);
                $target_file = $target_dir . $file_name;

                // Check if file is an image
                $check = getimagesize($_FILES['images']['tmp_name'][$key]);
                if ($check !== false) {
                    if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $target_file)) {
                        $uploaded_images[] = $file_name;
                    }
                }
            }
        }
    }

    // Convert images array to JSON
    $images_json = !empty($uploaded_images) ? json_encode($uploaded_images) : NULL;

    // Insert contribution into database
    $query = "INSERT INTO pending_contributions (user_id, category_id, title, content, excerpt, images, contact_info, status)
              VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";

    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "iisssss", $user_id, $category_id, $title, $content, $excerpt, $images_json, $contact_info);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['contribute-success'] = 'Your contribution has been submitted successfully and is pending approval.';
        // Clear form data
        unset($_SESSION['contribute-data']);
    } else {
        $_SESSION['contribute'] = 'Failed to submit contribution. Please try again.';
        $_SESSION['contribute-data'] = $_POST;
    }

    mysqli_stmt_close($stmt);
} else {
    // If not a POST request, redirect back
    header('location: ' . ROOT_URL . 'contribute.php');
    die();
}

header('location: ' . ROOT_URL . 'contribute.php');
die();
