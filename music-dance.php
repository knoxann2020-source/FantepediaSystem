<?php
session_start();
require 'config/database.php';

// Table check
$table_check = "SHOW TABLES LIKE 'fante_music_dance'";
if (mysqli_num_rows(mysqli_query($connection, $table_check)) == 0) {
    die('Database not ready. Contact admin.');
}

// Search/filter
$search = isset($_GET['search']) ? filter_var($_GET['search'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
$category_filter = isset($_GET['category']) ? filter_var($_GET['category'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';

// Query approved entries
$query = "SELECT * FROM fante_music_dance WHERE status = 'approved'";
$params = []; $types = '';
if ($search) {
    $query .= " AND (title LIKE ? OR description LIKE ?)";
    $params = ["%$search%", "%$search%"];
    $types = 'ss';
}
if ($category_filter) {
    $query .= " AND category = ?";
    $params[] = $category_filter;
    $types .= 's';
}
$query .= " ORDER BY created_at DESC";

$stmt = $params ? mysqli_prepare($connection, $query) : null;
$result = $params ? (mysqli_stmt_bind_param($stmt, $types, ...$params) && mysqli_stmt_execute($stmt) ? mysqli_stmt_get_result($stmt) : mysqli_query($connection, $query)) : mysqli_query($connection, $query);

// Stats
$stats = ['total' => 0, 'music' => 0, 'dance' => 0];
$q = "SELECT category, COUNT(*) c FROM fante_music_dance WHERE status='approved' GROUP BY category";
$r = mysqli_query($connection, $q);
while ($row = mysqli_fetch_assoc($r)) {
    $stats[$row['category']] = $row['c'];
    $stats['total'] += $row['c'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fante Music & Dance - Fantepedia</title>
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* From cloth.php */
        :root { --primary: #6f6af8; }
        .page-container { max-width: 1400px; margin: 0 auto; padding: 30px 20px; border-radius: 20px; background: white; box-shadow: 0 10px 40px rgba(0,0,0,0.1); margin-top: 8rem; }
        .hero-section { background: linear-gradient(135deg, var(--primary), #5854c7); border-radius: 20px; padding: 50px; margin-bottom: 40px; color: white; position: relative; overflow: hidden; }
        .hero-section h1 { font-size: 2.5rem; margin-bottom: 15px; display: flex; align-items: center; gap: 15px; }
        .hero-stats { display: flex; gap: 30px; margin-top: 30px; }
        .stat-item { background: rgba(255,255,255,0.15); padding: 15px 25px; border-radius: 12px; text-align: center; }
        .search-section { background: white; border-radius: 15px; padding: 25px; margin-bottom: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .search-form { display: flex; gap: 15px; flex-wrap: wrap; }
        .search-input-wrapper input { width: 100%; padding: 15px 20px 15px 50px; border: 2px solid #e0e0e0; border-radius: 10px; }
        .search-btn { padding: 15px 30px; background: var(--primary); color: white; border: none; border-radius: 10px; cursor: pointer; }
        .category-tabs { display: flex; gap: 15px; margin-bottom: 30px; flex-wrap: wrap; }
        .category-tab { padding: 12px 25px; border: none; border-radius: 25px; cursor: pointer; transition: all 0.3s; }
        .category-tab.active { background: var(--primary); color: white; }
        .content-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px,1fr)); gap: 25px; }
        .content-card { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); transition: all 0.4s; }
        .content-card:hover { transform: translateY(-10px); box-shadow: 0 15px 40px rgba(0,0,0,0.15); }
        .card-image { width: 100%; height: 220px; object-fit: cover; position: relative; }
        .media-badge { position: absolute; top: 10px; left: 10px; background: rgba(255,255,255,0.9); padding: 5px 8px; border-radius: 15px; font-size: 0.8rem; box-shadow: 0 2px 10px rgba(0,0,0,0.2); }
        .card-category { position: absolute; top: 15px; right: 15px; padding: 8px 15px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; color: white; }
        .card-content { padding: 20px; }
        .card-title { font-size: 1.3rem; margin-bottom: 10px; }
        .card-description { color: #666; line-height: 1.6; margin-bottom: 15px; display: -webkit-box; -webkit-line-clamp: 3; line-clamp: 3; overflow: hidden; }
        .card-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 15px; border-top: 1px solid #e0e0e0; }
        .view-btn { padding: 8px 20px; background: var(--primary); color: white; border: none; border-radius: 20px; cursor: pointer; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); }
        .modal-content { background: white; margin: 5% auto; padding: 0; border-radius: 15px; max-width: 900px; max-height: 90vh; overflow-y: auto; }
        .modal-header { padding: 25px 30px; border-bottom: 1px solid #e0e0e0; position: relative; }
        .close-modal { position: absolute; right: 25px; top: 25px; font-size: 1.5rem; cursor: pointer; }
        .modal-body { padding: 30px; }
        .modal-image { width: 100%; max-height: 400px; object-fit: cover; border-radius: 10px; margin-bottom: 20px; }
        .contribution-btn { position: fixed; bottom: 30px; right: 30px; padding: 15px 30px; background: linear-gradient(135deg, var(--success), #21867a); color: white; border-radius: 30px; box-shadow: 0 5px 20px rgba(39,174,96,0.4); font-weight: 600; }
        @media (max-width: 768px) { .hero-stats { flex-direction: column; gap: 1rem; } .search-form { flex-direction: column; } .content-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<?php include 'partials/header.php'; ?>


<section class="page-container">
    <a href="<?= ROOT_URL ?>services.php" style="display: inline-flex; align-items: center; gap: 8px; color: var(--primary); font-weight: 500; margin-bottom: 20px; text-decoration: none;">
        <i class="fas fa-arrow-left"></i> Back to Services
    </a>

    <!-- Hero -->
    <div class="hero-section">
        <div>
            <h1><i class="fas fa-music"></i> Fante Music & Dance</h1>
            <p>Discover traditional Fante music and dance - the heartbeat of Fante culture</p>
            <div class="hero-stats">
                <div class="stat-item">
                    <div class="number"><?= $stats['total'] ?></div>
                    <div class="label">Total Entries</div>
                </div>
                <div class="stat-item">
                    <div class="number"><?= $stats['Music'] ?? 0 ?></div>
                    <div class="label">Music</div>
                </div>
                <div class="stat-item">
                    <div class="number"><?= $stats['Dance'] ?? 0 ?></div>
                    <div class="label">Dance</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search -->
    <div class="search-section">
        <form method="GET" class="search-form">
            <div class="search-input-wrapper">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Search by title..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <select name="category">
                <option value="">All Categories</option>
                <option value="Music" <?= $category_filter == 'Music' ? 'selected' : '' ?>>Music</option>
                <option value="Dance" <?= $category_filter == 'Dance' ? 'selected' : '' ?>>Dance</option>
            </select>
            <button type="submit" class="search-btn"><i class="fas fa-search"></i> Search</button>
            <?php if ($search || $category_filter): ?>
                <a href="<?= ROOT_URL ?>music-dance.php" class="search-btn" style="background: #666;">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Tabs -->
    <div class="category-tabs">
        <a href="?<?= http_build_query(array_diff_key($_GET, ['category'=> ''])) ?>" class="category-tab <?= empty($category_filter) ? 'active' : '' ?>">
            <i class="fas fa-th"></i> All (<?= $stats['total'] ?>)
        </a>
        <a href="?<?= http_build_query(['category' => 'Music'] + array_diff_key($_GET, ['category'=> ''])) ?>" class="category-tab <?= $category_filter == 'Music' ? 'active' : '' ?>">
            <i class="fas fa-music"></i> Music (<?= $stats['Music'] ?? 0 ?>)
        </a>
        <a href="?<?= http_build_query(['category' => 'Dance'] + array_diff_key($_GET, ['category'=> ''])) ?>" class="category-tab <?= $category_filter == 'Dance' ? 'active' : '' ?>">
            <i class="fas fa-dancing"></i> Dance (<?= $stats['Dance'] ?? 0 ?>)
        </a>
    </div>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="content-grid">
            <?php while ($entry = mysqli_fetch_assoc($result)): ?>
                <div class="content-card" onclick="openModal(<?= $entry['id'] ?>)">
                    <?php if ($entry['image']): ?>
                        <img src="<?= ROOT_URL . $entry['image'] ?>" alt="<?= htmlspecialchars($entry['title']) ?>" class="card-image">
                        <?php if ($entry['audio']): ?><span class="media-badge"><i class="fas fa-music" title="Audio Available"></i></span><?php endif; ?>
                        <?php if ($entry['video']): ?><span class="media-badge"><i class="fas fa-video" title="Video Available"></i></span><?php endif; ?>
                    <?php else: ?>
                        <div class="card-image" style="background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; position: relative;">
                            <?= $entry['category'] == 'Music' ? '🎵' : '💃' ?>
                            <?php if ($entry['audio']): ?><span class="media-badge" style="position: absolute; top: 10px; right: 10px;"><i class="fas fa-music"></i></span><?php endif; ?>
                            <?php if ($entry['video']): ?><span class="media-badge" style="position: absolute; top: 10px; right: 10px;"><i class="fas fa-video"></i></span><?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <span class="card-category <?= $entry['category'] == 'Music' ? 'category-food-badge' : 'category-cloth-badge' ?>">
                        <?= $entry['category'] == 'Music' ? '🎵' : '💃' ?> <?= htmlspecialchars($entry['category']) ?>
                    </span>
                    <div class="card-content">
                        <h3 class="card-title"><?= htmlspecialchars($entry['title']) ?></h3>
                        <p class="card-description"><?= htmlspecialchars(substr($entry['description'], 0, 150)) ?>...</p>
                        <div class="card-footer">
                            <span class="card-date"><i class="far fa-calendar-alt"></i> <?= date('M d, Y', strtotime($entry['created_at'])) ?></span>
                            <button class="view-btn"><i class="fas fa-eye"></i> View</button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 60px; background: white; border-radius: 20px;">
            <i class="fas fa-music" style="font-size: 4rem; color: #ddd;"></i>
            <h3>No entries yet</h3>
            <p>Be the first to contribute Fante music and dance!</p>
            <?php if (isset($_SESSION['user-id'])): ?>
                <a href="<?= ROOT_URL ?>music-dance-input.php" class="btn" style="background: var(--primary);">Contribute Now</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<!-- Modal -->
<div id="modal" class="modal" onclick="closeModal(event)">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h2 id="modal-title"></h2>
            <span class="close-modal" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <img id="modal-image" style="display: none;">
            <div id="modal-category" style="margin-bottom: 15px; font-weight: 600;"></div>
            <p id="modal-desc"></p>
            <div id="modal-media"></div>
        </div>
    </div>
</div>

<!-- Contribution FAB -->
<?php if (isset($_SESSION['user-id'])): ?>
    <a href="<?= ROOT_URL ?>music-dance-input.php" class="contribution-btn" title="Contribute">
        <i class="fas fa-plus"></i> Contribute
    </a>
<?php endif; ?>

<script>
const entries = <?= json_encode(array_column(array_map('mysqli_fetch_assoc', array_fill(0, mysqli_num_rows($result), null)), null, 'id') ?: '[]') ?>;
function openModal(id) {
    const entry = entries[id];
    document.getElementById('modal-title').textContent = entry.title;
    document.getElementById('modal-desc').textContent = entry.description;
    const catEl = document.getElementById('modal-category');
    catEl.textContent = (entry.category === 'Music' ? '🎵' : '💃') + ' ' + entry.category;
    catEl.className = 'card-category ' + (entry.category === 'Music' ? 'category-food-badge' : 'category-cloth-badge');
    
    const img = document.getElementById('modal-image');
    img.style.display = entry.image ? 'block' : 'none';
    if (entry.image) img.src = '<?= ROOT_URL ?>' + entry.image;
    
    const media = document.getElementById('modal-media');
    media.innerHTML = '';
    if (entry.video) {
        const vid = document.createElement('video');
        vid.src = '<?= ROOT_URL ?>' + entry.video;
        vid.controls = true;
        media.appendChild(vid);
    }
    if (entry.audio) {
        const aud = document.createElement('audio');
        aud.src = '<?= ROOT_URL ?>' + entry.audio;
        aud.controls = true;
        media.appendChild(aud);
    }
    
    document.getElementById('modal').style.display = 'block';
}
function closeModal(e) {
    if (e) e.stopPropagation();
    document.getElementById('modal').style.display = 'none';
}
</script>

<?php include 'partials/footer.php'; ?>

<?php if (isset($_SESSION['add-post-success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <?= $_SESSION['add-post-success'] ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['add-post-success']); endif; ?>

<?php if (isset($_SESSION['edit-post-success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <?= $_SESSION['edit-post-success'] ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['edit-post-success']); endif; ?>

<?php if (isset($_SESSION['delete-post-success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <?= $_SESSION['delete-post-success'] ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['delete-post-success']); endif; ?>

<?php if (isset($_SESSION['delete-post'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <?= $_SESSION['delete-post'] ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['delete-post']); endif; ?>
</body>
</html>
