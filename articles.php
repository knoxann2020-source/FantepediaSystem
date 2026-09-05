<?php
session_start();
require 'config/constants.php';
require 'config/database.php';

include 'partials/header.php';

// Fetch recent posts for the blog section
$query = "SELECT p.*, u.username, u.avatar, c.title AS category FROM posts p JOIN users u ON p.user_id = u.id JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC LIMIT 10";
$result = mysqli_query($connection, $query);
$posts = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<section class="blog-section">
    <div class="container">
        <h1 class="blog-title">Latest Articles</h1>
        <p class="blog-subtitle">Stay updated with our latest posts and insights</p>

        <div class="posts__container">
            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
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
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert__message error">
                    <p>No articles available at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
include 'partials/footer.php';
?>
