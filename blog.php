<?php
session_start();
require 'config/constants.php';
require 'config/database.php';

include 'partials/header.php';

// Get filter from URL
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'recent';

// Define valid filters
$valid_filters = ['safety', 'repair', 'recent', 'popular', 'categories', 'tags'];

if (!in_array($filter, $valid_filters)) {
    $filter = 'recent';
}

// Build query based on filter
switch ($filter) {
    case 'safety':
        $query = "SELECT p.*, u.username, u.avatar, c.title AS category FROM posts p JOIN users u ON p.user_id = u.id JOIN categories c ON p.category_id = c.id WHERE p.title LIKE '%safety%' OR p.body LIKE '%safety%' ORDER BY p.created_at DESC";
        break;
    case 'repair':
        $query = "SELECT p.*, u.username, u.avatar, c.title AS category FROM posts p JOIN users u ON p.user_id = u.id JOIN categories c ON p.category_id = c.id WHERE p.title LIKE '%repair%' OR p.body LIKE '%repair%' ORDER BY p.created_at DESC";
        break;
    case 'recent':
        $query = "SELECT p.*, u.username, u.avatar, c.title AS category FROM posts p JOIN users u ON p.user_id = u.id JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC LIMIT 10";
        break;
    case 'popular':
        $query = "SELECT p.*, u.username, u.avatar, c.title AS category FROM posts p JOIN users u ON p.user_id = u.id JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC LIMIT 10"; // You might want to add a view count column for popularity
        break;
    case 'categories':
        $query = "SELECT p.*, u.username, u.avatar, c.title AS category FROM posts p JOIN users u ON p.user_id = u.id JOIN categories c ON p.category_id = c.id ORDER BY c.title ASC";
        break;
    case 'tags':
        $query = "SELECT p.*, u.username, u.avatar, c.title AS category FROM posts p JOIN users u ON p.user_id = u.id JOIN categories c ON p.category_id = c.id WHERE p.tags IS NOT NULL AND p.tags != '' ORDER BY p.created_at DESC";
        break;
    default:
        $query = "SELECT p.*, u.username, u.avatar, c.title AS category FROM posts p JOIN users u ON p.user_id = u.id JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC LIMIT 10";
}

$result = mysqli_query($connection, $query);
$posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Get filter title
$filter_titles = [
    'safety' => 'Safety Posts',
    'repair' => 'Repair Posts',
    'recent' => 'Recent Posts',
    'popular' => 'Popular Posts',
    'categories' => 'Posts by Category',
    'tags' => 'Tagged Posts'
];

$filter_title = isset($filter_titles[$filter]) ? $filter_titles[$filter] : 'Blog Posts';
?>

<section class="category__title">
    <h2><?php echo htmlspecialchars($filter_title); ?></h2>
    <p>Explore our blog posts filtered by <?php echo htmlspecialchars($filter); ?>.</p>
</section>

<?php if ($filter === 'recent'): ?>
<section class="featured-posts">
    <div class="container featured-posts__container">
        <div class="featured-posts__header">
            <h3>🎲 Featured Post</h3>
            <div class="featured-controls">
                <div id="timer-display" class="timer" style="display: none;">Next shuffle in: 5:00</div>
                <button id="shuffle-now" class="shuffle-btn">🔄 Shuffle Now</button>
            </div>
        </div>
        <div id="featured-loading" class="loading-spinner" style="display: none;">
            <div class="spinner"></div>
            <p>Loading featured post...</p>
        </div>
        <div id="featured-post-container" class="featured-post-container">
            <!-- Featured post will be loaded here via JavaScript -->
        </div>
    </div>
</section>
<?php endif; ?>

<style>
        .posts-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            border: 5px solid rgba(166, 173, 62, 0.1);
            border-radius: 10px;
            box-shadow: inset 0 0 10px rgba(166, 173, 62, 0.1);
            padding: 2rem;
        }

        @media (max-width: 1024px) {
            .posts-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .posts-grid {
                grid-template-columns: 1fr;
                padding: 1rem;
            }
        }

        .posts-grid .content-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .posts-grid .content-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
    </style>

<section class="posts">
    <div class="container posts-grid modern-grid">
        <div class="modern-hero" style="grid-column: 1 / -1;">
            <h1><?= htmlspecialchars($filter_title) ?></h1>
            <p>Explore our latest articles and posts</p>
        </div>
        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
<article class="post content-card">
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
                        <div class="post__user">
                            <div class="post__user-avatar">
                                <img src="./images/<?php echo $post['avatar']; ?>" alt="<?php echo htmlspecialchars($post['username']); ?>">
                            </div>
                            <div class="post__user-info">
                                <h5>By: <?php echo htmlspecialchars($post['username']); ?></h5>
                                <small><?php echo date("M d, Y - H:i", strtotime($post['created_at'])); ?></small>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert__message error">
                <p>No posts found for this filter.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
// Featured post functionality
let shuffleTimer;
let timeLeft = 300; // 5 minutes in seconds

function loadFeaturedPost() {
    const container = document.getElementById('featured-post-container');
    const loading = document.getElementById('featured-loading');

    if (loading) loading.style.display = 'block';

    fetch('get_random_posts.php')
        .then(response => response.json())
        .then(posts => {
            if (loading) loading.style.display = 'none';

            if (posts && posts.length > 0) {
                // Get a random post from the array
                const randomIndex = Math.floor(Math.random() * posts.length);
                const post = posts[randomIndex];

                const postHTML = `
                    <article class="featured-post-card">
                        <div class="featured-post__thumbnail">
                            <img src="./images/${post.thumbnail}" alt="${post.title}" onerror="this.src='./images/default-avatar.svg'">
                        </div>
                        <div class="featured-post__info">
                            <a href="category.php?category=${encodeURIComponent(post.category)}" class="category__button">${post.category}</a>
                            <h3 class="featured-post__title">
                                <a href="post.php?id=${post.id}">${post.title}</a>
                            </h3>
                            <p class="featured-post__body">
                                ${post.body.substring(0, 200)}...
                            </p>
                            <div class="featured-post__user">
                                <div class="featured-post__user-avatar">
                                    <img src="./images/${post.avatar}" alt="${post.username}" onerror="this.src='./images/default-avatar.svg'">
                                </div>
                                <div class="featured-post__user-info">
                                    <h5>By: ${post.username}</h5>
                                    <small>${new Date(post.created_at).toLocaleDateString('en-US', {
                                        year: 'numeric',
                                        month: 'short',
                                        day: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    })}</small>
                                </div>
                            </div>
                        </div>
                    </article>
                `;

                container.innerHTML = postHTML;
            } else {
                container.innerHTML = '<div class="alert__message error"><p>No posts available for featuring.</p></div>';
            }
        })
        .catch(error => {
            console.error('Error loading featured post:', error);
            if (loading) loading.style.display = 'none';
            container.innerHTML = '<div class="alert__message error"><p>Error loading featured post. Please try again.</p></div>';
        });
}

function updateTimer() {
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    const timerDisplay = document.getElementById('timer-display');

    if (timerDisplay) {
        timerDisplay.textContent = `Next shuffle in: ${minutes}:${seconds.toString().padStart(2, '0')}`;
    }

    timeLeft--;

    if (timeLeft < 0) {
        timeLeft = 300; // Reset to 5 minutes
        loadFeaturedPost();
    }
}

function startTimer() {
    if (shuffleTimer) clearInterval(shuffleTimer);
    shuffleTimer = setInterval(updateTimer, 1000);
}

function stopTimer() {
    if (shuffleTimer) {
        clearInterval(shuffleTimer);
        shuffleTimer = null;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if we're on the recent posts page
    if (window.location.search.includes('filter=recent') || window.location.search === '') {
        loadFeaturedPost();
        // startTimer(); // Commented out to prevent shaking

        // Shuffle now button
        const shuffleBtn = document.getElementById('shuffle-now');
        if (shuffleBtn) {
            shuffleBtn.addEventListener('click', function() {
                // timeLeft = 300; // Reset timer
                loadFeaturedPost();
            });
        }
    }
});

// Cleanup timer when page unloads
window.addEventListener('beforeunload', stopTimer);
</script>

<?php
include 'partials/footer.php';
?>
