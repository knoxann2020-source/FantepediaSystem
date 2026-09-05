<?php
session_start();
require 'config/database.php';

// Get all categories
$categories_query = "SELECT id, title FROM categories ORDER BY id";
$categories_result = mysqli_query($connection, $categories_query);

$random_posts = [];

if ($categories_result && mysqli_num_rows($categories_result) > 0) {
    while ($category = mysqli_fetch_assoc($categories_result)) {
        // Get a random post from this category
        $post_query = "SELECT p.*, u.username, u.avatar, c.title AS category
                       FROM posts p
                       JOIN users u ON p.user_id = u.id
                       JOIN categories c ON p.category_id = c.id
                       WHERE p.category_id = ?
                       ORDER BY RAND()
                       LIMIT 1";
        $stmt = mysqli_prepare($connection, $post_query);
        mysqli_stmt_bind_param($stmt, 'i', $category['id']);
        mysqli_stmt_execute($stmt);
        $post_result = mysqli_stmt_get_result($stmt);

        if ($post_result && mysqli_num_rows($post_result) > 0) {
            $post = mysqli_fetch_assoc($post_result);
            $random_posts[] = $post;
        }
    }
}

// Shuffle the posts to randomize the order
shuffle($random_posts);

// Return as JSON
header('Content-Type: application/json');
echo json_encode($random_posts);
?>
