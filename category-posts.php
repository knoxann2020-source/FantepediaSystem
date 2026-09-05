<?php
include 'partials/header.php';

// fetch category from categories table using id passed in url
if(isset($_GET['id'])) {
    $category_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $category_id = mysqli_real_escape_string($connection, $category_id);
// Category removed - show all approved posts
$category = ['title' => 'All Posts'];

// Check if this is a special category that needs custom handling
$special_categories = [
    'Virtual Museum' => 'fante_artifacts',
    'Fante Ceremonies' => 'fante_ceremonies',
    'Fante History' => 'fante_history',
    'Fante States' => 'fante_states'
];

if (array_key_exists($category['title'], $special_categories)) {
    // Handle special categories
    $table = $special_categories[$category['title']];
    // Adjust column names based on table structure
    if($table == 'fante_history') {
        $posts_query = "SELECT id, title, details AS body, '' AS thumbnail, created_at AS date_time, user_id FROM $table WHERE status='approved' ORDER BY created_at DESC";
    } elseif($table == 'fante_states') {
        $posts_query = "SELECT id, state_name AS title, details AS body, '' AS thumbnail, created_at AS date_time, user_id FROM $table WHERE status='approved' ORDER BY created_at DESC";
    } else {
        $posts_query = "SELECT id, title, description AS body, image AS thumbnail, created_at AS date_time, user_id FROM $table WHERE status='approved' ORDER BY created_at DESC";
    }
    $posts = mysqli_query($connection, $posts_query);

    // Randomly select one item for featuring if there are multiple
    if ($posts && mysqli_num_rows($posts) > 1) {
        $all_posts = mysqli_fetch_all($posts, MYSQLI_ASSOC);
        $random_index = array_rand($all_posts);
        $featured_post = $all_posts[$random_index];
        $posts = [$featured_post]; // Convert to array for consistency
    }
} else {
    // fetch posts for regular categories
    $posts_query = "SELECT * FROM posts WHERE date_time IS NOT NULL ORDER BY date_time DESC LIMIT 20";
    $posts = mysqli_query($connection, $posts_query);
}
} else {
    // redirect to home page if no id is provided
    header('location: ' . ROOT_URL . 'index.php');
    die();
}

?>
    <header class="category__title">
        <h2><?= htmlspecialchars($category['title']) ?></h2>
    </header>
    <!--======================================END OF CATEGORY TITLE================================-->

<?php if(mysqli_num_rows($posts) > 0):
?>
 <section class="posts">
        <div class="container posts__container">
            <?php while($post = mysqli_fetch_assoc($posts)) : ?>

            <article class="post">
                <div class="post__thumbnail">
                    <img src="./images/<?= $post['thumbnail'] ?>">
                </div>
                <div class="post__info">
                     <!-- fetch category from categories table using category_id of post -->
                    <h3 class="post__title">
                        <a href="<?= ROOT_URL ?>post.php?id=<?= $post['id'] ?>"><?= $post['title'] ?></a>
                    </h3>
                    <p class="post__body">
                       <?= substr($post['body'], 0, 150) ?>...
                     </p>
                    <div class="post__user">
                        <div class="post__user-avatar">
                            <?php
                            // fetch user from users table using user_id of post
                            $user_id = (int) $post['user_id'];
                            $user_query = "SELECT * FROM users WHERE id=$user_id";
                            $user_result = mysqli_query($connection, $user_query);
                            $user = $user_result ? mysqli_fetch_assoc($user_result) : null;
                            $user = $user ?: [
                                'avatar' => 'default-avatar.png',
                                'firstname' => 'Unknown',
                                'lastname' => 'User'
                            ];

                             ?>
                            <img src="./images/<?= $user['avatar'] ?>">
                        </div>
                        <div class="post__user-info">
                           <h5><i>by: <?= "{$user['firstname']} {$user['lastname']}" ?></i></h5>
                            <small><i>
                                <?= date("M d, Y - H:i", strtotime($post['date_time'])) ?>
                            </i></small>
                        </div>
                    </div>
                </div>
            </article>
            <?php endwhile ?>
        </div>

    </section>

    </section>
    <?php else : ?>
        <div class="alert__message error lg">
            <p>No posts found for this category</p>
        </div>
    <?php endif ?>

    <!-======================================END POST================================>

     <section class="category__button">
        <div class="container category__button-container">
            <?php
            // Category nav removed
            ?>
        </div>
</section>


    <!--==================================END OF CATEGORY BUTTON==============================-->

   <?php
include 'partials/footer.php';
?>

