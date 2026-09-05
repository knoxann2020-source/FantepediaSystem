<?php
session_start();
require 'config/constants.php';
require 'config/database.php';

// Define valid categories
$valid_categories = [
    'Fante Alphabets',
    'Fante Phonetics',
    'Language Tutorial',
    'Add History',
    'Fante Panapoly',
    'Fante States',
    'Fante History',
    'Virtual Museum',
    'Fante Ceremonies'
];

// Get category from URL
$category = isset($_GET['category']) ? urldecode($_GET['category']) : '';

// All categories page (no validation)
$category = 'Featured Posts';

include 'partials/header.php';

// Get posts for this category
$query = "SELECT p.*, u.username, u.avatar, c.title AS category FROM posts p JOIN users u ON p.user_id = u.id JOIN categories c ON p.category_id = c.id WHERE c.title = ? ORDER BY p.created_at DESC";
$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, 's', $category);
mysqli_stmt_execute($stmt);
$posts = mysqli_stmt_get_result($stmt);

// Get category description (you might want to store this in a database table)
$category_descriptions = [
    'Fante Alphabets' => 'Learn the Fante alphabet and its pronunciation.',
    'Fante Phonetics' => 'Explore the phonetic sounds of the Fante language.',
    'Language Tutorial' => 'Step-by-step tutorials for learning Fante.',
    'Add History' => 'Historical additions and updates to Fante culture.',
    'Fante Panapoly' => 'Comprehensive overview of Fante language and culture.',
    'Fante States' => 'Information about Fante states and regions.',
    'Fante History' => 'The rich history of the Fante people.',
    'Virtual Museum' => 'Digital exhibits of Fante artifacts and culture.',
    'Fante Ceremonies' => 'Traditional Fante ceremonies and rituals.'
];

$category_description = isset($category_descriptions[$category]) ? $category_descriptions[$category] : '';
?>

<section class="category__title">
    <h2><?php echo htmlspecialchars($category); ?></h2>
    <p><?php echo htmlspecialchars($category_description); ?></p>
</section>

<section class="posts">
    <div class="container posts__container">
        <?php if (mysqli_num_rows($posts) > 0): ?>
            <?php while ($post = mysqli_fetch_assoc($posts)): ?>
                <article class="post">
                    <div class="post__thumbnail">
                        <img src="./images/<?php echo $post['thumbnail']; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                    </div>
                    <div class="post__info">
                        <a href="category.php?category=<?php echo urlencode($post['category']); ?>" class="category__button"><?php echo htmlspecialchars($post['category']); ?></a>
                        <h3 class="post__title">
                            <a href="post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a>
                        </h3>
                        <p class="post__body">
                            <?php echo htmlspecialchars(substr($post['body'], 0, 150)) . '...'; ?>
                        </p>
                        <div class="post__author">
                            <div class="post__author-avatar">
                                <img src="./images/<?php echo $post['avatar']; ?>" alt="<?php echo htmlspecialchars($post['username']); ?>">
                            </div>
                            <div class="post__author-info">
                                <h5>By: <?php echo htmlspecialchars($post['username']); ?></h5>
                                <small><?php echo date("M d, Y - H:i", strtotime($post['created_at'])); ?></small>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="alert__message error">
                <p>No posts found in this category.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
include 'partials/footer.php';
?>
