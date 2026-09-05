<?php
// Start session at the very beginning - before ANY output
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and NOT admin
if (!isset($_SESSION['user-id']) || isset($_SESSION['user_is_admin'])) {
    header('location: ' . ROOT_URL . 'signin.php');
    die();
}

// Include config and database
require 'config/constants.php';
require 'config/database.php';

// fetch current user's posts from database
$current_user_id = $_SESSION['user-id'];
$user_query = "SELECT id, title, category_id, created_at FROM posts WHERE user_id = $current_user_id ORDER BY id DESC";
$posts = mysqli_query($connection, $user_query);

// fetch user's contributions summary (if tables exist)
$contributions_count = 0;
$history_count = 0;
$ceremonies_count = 0;
$artifacts_count = 0;
$states_count = 0;

// fetch user's contributions summary - check table existence first
$contributions_count = 0;
$history_count = 0;
$ceremonies_count = 0;
$artifacts_count = 0;
$states_count = 0;

$tables = [
    'pending_contributions' => &$contributions_count,
    'fante_history' => &$history_count,
    'fante_ceremonies' => &$ceremonies_count,
    'fante_artifacts' => &$artifacts_count,
    'fante_states' => &$states_count
];

foreach ($tables as $table => &$count_var) {
    $table_check = "SHOW TABLES LIKE '$table'";
    $table_result = mysqli_query($connection, $table_check);
    if (mysqli_num_rows($table_result) > 0) {
        $count_query = "SELECT COUNT(*) as count FROM $table WHERE user_id = $current_user_id";
        $count_result = mysqli_query($connection, $count_query);
        if ($count_result) {
            $count_var = mysqli_fetch_assoc($count_result)['count'];
        }
    }
}

// Fetch user profile info
$user_profile_query = "SELECT firstname, lastname, username, email, avatar FROM users WHERE id = $current_user_id";
$user_profile = mysqli_fetch_assoc(mysqli_query($connection, $user_profile_query));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE-edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Fantepedia System</title>
    <link rel="icon" type="image/svg+xml" href="<?= ROOT_URL ?>images/default-avatar.svg">
<link rel="stylesheet" href="<?= ROOT_URL ?>css/style.css">
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/modern-research.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <section class="dashboard"> 
        <!-- Alert Messages -->
        <?php if (isset($_SESSION['add-post-success'])) : ?>
            <div class="alert__message success"><p><?= $_SESSION['add-post-success']; unset($_SESSION['add-post-success']); ?></p></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['contribution-success'])) : ?>
            <div class="alert__message success"><p><?= $_SESSION['contribution-success']; unset($_SESSION['contribution-success']); ?></p></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['contribution-error'])) : ?>
            <div class="alert__message error"><p><?= $_SESSION['contribution-error']; unset($_SESSION['contribution-error']); ?></p></div>
        <?php endif; ?>

        <div class="container dashboard__container section__extra-margin">
            <button id="show__sidebar-btn" class="sidebar__toggle"><i class="uil uil-angle-right-b"></i></button>
            <button id="hide__sidebar-btn" class="sidebar__toggle"><i class="uil uil-angle-left-b"></i></button>

            <aside>
                <ul>
<li><a href="<?= ROOT_URL ?>user-contribution.php"><i class="uil uil-book-open"></i><h5>Contribute Content</h5></a></li>
                    <li><a href="<?= ROOT_URL ?>user-input.php"><i class="uil uil-edit"></i><h5>User Input Forms</h5></a></li>
                    <li><a href="<?= ROOT_URL ?>user-history-input.php"><i class="uil uil-history"></i><h5>History Input</h5></a></li>
                    <li><a href="<?= ROOT_URL ?>user-ceremonies-input.php"><i class="uil uil-calendar-alt"></i><h5>Ceremonies Input</h5></a></li>
                    <li><a href="<?= ROOT_URL ?>user-artifacts-input.php"><i class="uil uil-image"></i><h5>Artifacts Input</h5></a></li>
                    <li><a href="<?= ROOT_URL ?>user-states-input.php"><i class="uil uil-map-marker"></i><h5>States Input</h5></a></li>
                    <li><a href="<?= ROOT_URL ?>profile.php"><i class="uil uil-user"></i><h5>Profile</h5></a></li>
                </ul>
            </aside>

            <main>
                <h2>Welcome, <?= htmlspecialchars($user_profile['firstname'] . ' ' . $user_profile['lastname']) ?>!</h2>
                
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <h3><?= mysqli_num_rows($posts) ?></h3>
                        <p>Your Posts</p>
                    </div>
                    <div class="stat-card">
                        <h3><?= $contributions_count ?></h3>
                        <p>Contributions</p>
                    </div>
                    <div class="stat-card">
                        <h3><?= $history_count ?></h3>
                        <p>History Entries</p>
                    </div>
                    <div class="stat-card">
                        <h3><?= $ceremonies_count ?></h3>
                        <p>Ceremonies</p>
                    </div>
                </div>

                <div class="dashboard-section">
                    <h3>Your Recent Posts</h3>
                    <?php if($posts && mysqli_num_rows($posts) > 0) : ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($post = mysqli_fetch_assoc($posts)) : 
                                    $category_id = $post['category_id'];
                                    $cat_query = "SELECT title FROM categories WHERE id = $category_id";
                                    $cat_result = mysqli_query($connection, $cat_query);
                                    $category = mysqli_fetch_assoc($cat_result);
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($post['title']) ?></td>
                                        <td><?= date('M j, Y', strtotime($post['created_at'])) ?></td>
                                        <td>
                                            <span class="text-muted">Contact Admin for changes</span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <div class="alert__message info">
                            <p>No posts yet. <a href="<?= ROOT_URL ?>admin/add-post.php">Create your first post!</a></p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="quick-actions">
                    <h3>Quick Actions</h3>
                    <div class="action-grid">

                        <a href="<?= ROOT_URL ?>user-contribution.php" class="action-card">
                            <i class="uil uil-edit"></i>
                            <span>Contribute</span>
                        </a>
                        <a href="<?= ROOT_URL ?>profile.php" class="action-card">
                            <i class="uil uil-setting"></i>
                            <span>Settings</span>
                        </a>
                    </div>
                </div>
            </main>
        </div>
    </section>

    <?php include 'partials/footer.php'; ?>

    <script src="<?= ROOT_URL ?>js/main.js"></script>
    <style>
        .dashboard-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin: 2rem 0; }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem; border-radius: 10px; text-align: center; }
        .stat-card h3 { font-size: 2rem; margin: 0 0 0.5rem 0; }
        .dashboard-section { margin: 2rem 0; }
        .action-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
        .action-card { display: flex; flex-direction: column; align-items: center; padding: 2rem; background: #f8f9fa; border-radius: 10px; text-decoration: none; color: inherit; transition: transform 0.3s; }
        .action-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .action-card i { font-size: 3rem; color: #667eea; margin-bottom: 1rem; }
        @media (max-width: 768px) { .dashboard-stats { grid-template-columns: repeat(2, 1fr); } }
    </style>
</body>
</html>

