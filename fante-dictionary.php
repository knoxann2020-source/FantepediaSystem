<?php
require_once 'config/constants.php';
require_once 'config/database.php';

// Search handling
$search = '';
if (isset($_GET['search'])) {
    $search = '%' . mysqli_real_escape_string($connection, $_POST['search'] ?? $_GET['search']) . '%';
}

// Fetch approved dictionary entries
$where = "status = 'approved'";
$params = [];
$types = 's';
if ($search) {
    $where .= " AND (word LIKE ? OR meaning LIKE ?)";
    $params = [$search, $search];
    $types = 'ss';
}

$query = "SELECT * FROM fante_dictionary WHERE $where ORDER BY word ASC";
$stmt = mysqli_prepare($connection, $query);
if ($search) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$entries = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fante Dictionary - Fantepedia</title>
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'partials/header.php'; ?>
    <section class="breadcrumb section extra-margin">
    <main class="dictionary-page">
        <div class="container">
            <div class="hero-section" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/pusuban.webp'); background-size: cover; background-position: center; padding: 5rem 2rem; border-radius: 20px; margin-bottom: 3rem; color: white; text-align: center;">
                <h1><i class="fas fa-book-dictionary"></i> Fante Dictionary</h1>
                <p>Explore authentic Fante words, meanings, origins, and pronunciations.</p>
                
                <!-- Search Form -->
                <form method="GET" class="search-form">
                    <div class="search-container">
                        <input type="text" name="search" placeholder="Search words or meanings..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </div>
                    <?php if ($search): ?>
                        <p style="margin-top: 1rem; opacity: 0.9;">Found <?= count($entries) ?> results for "<?= htmlspecialchars(trim($_GET['search'])) ?>"</p>
                    <?php endif; ?>
                </form>
            </div>

            <?php if (empty($entries)): ?>
                <div class="empty-state">
                    <i class="fas fa-search fa-3x" style="color: #ccc; margin-bottom: 1rem;"></i>
                    <h3>No entries found</h3>
                    <p>Try searching for a different word or meaning.</p>
                </div>
            <?php else: ?>
                <div class="dictionary-grid">
                    <?php foreach ($entries as $entry): ?>
                        <div class="entry-card">
                            <div class="entry-header">
                                <h3><?= htmlspecialchars($entry['word']) ?></h3>
                                <?php if ($entry['pronunciation']): ?>
                                    <audio controls style="width: 150px;">
                                        <source src="<?= ROOT_URL ?>images/<?= htmlspecialchars($entry['pronunciation']) ?>" type="audio/mpeg">
                                        Your browser does not support audio.
                                    </audio>
                                <?php endif; ?>
                            </div>
                            
                            <div class="entry-meaning">
                                <strong>Meaning:</strong> <?= htmlspecialchars($entry['meaning']) ?>
                            </div>
                            
                            <?php if ($entry['origin']): ?>
                                <div class="entry-origin">
                                    <strong>Origin:</strong> <?= htmlspecialchars($entry['origin']) ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="entry-media">
                                <?php if ($entry['image']): ?>
                                    <img src="<?= ROOT_URL ?>images/<?= htmlspecialchars($entry['image']) ?>" alt="<?= htmlspecialchars($entry['word']) ?>" loading="lazy">
                                <?php endif; ?>
                            </div>
                            
                            <div class="entry-meta">
                                <small>Added <?= date('M j, Y', strtotime($entry['created_at'])) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
    </section>

    <?php include 'partials/footer.php'; ?>

    <style>
        .dictionary-page { padding: 2rem 0; min-height: calc(100vh - 200px); }
        .hero-section h1 { font-size: 3.5rem; margin-bottom: 1rem; text-shadow: 2px 2px 4px rgba(0,0,0,0.5); }
        .hero-section p { font-size: 1.3rem; margin-bottom: 2rem; }
        .search-form { max-width: 600px; margin: 0 auto; }
        .search-container { position: relative; display: flex; }
        .search-container input { width: 100%; padding: 1.2rem 1.5rem 1.2rem 4rem; border: none; border-radius: 50px; font-size: 1.1rem; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .search-container button { position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: white; font-size: 1.2rem; cursor: pointer; }
        .dictionary-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 2rem; margin-top: 2rem; }
        .entry-card { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-radius: 20px; padding: 2rem; box-shadow: 0 20px 40px rgba(0,0,0,0.1); transition: all 0.3s ease; border: 1px solid rgba(255,255,255,0.3); }
        .entry-card:hover { transform: translateY(-10px); box-shadow: 0 30px 60px rgba(0,0,0,0.15); }
        .entry-header h3 { color: #333; margin-bottom: 1rem; font-size: 2rem; }
        .entry-meaning { margin-bottom: 1rem; font-size: 1.1rem; line-height: 1.6; color: #555; }
        .entry-origin { margin-bottom: 1rem; color: #666; font-style: italic; }
        .entry-media { margin: 1.5rem 0; text-align: center; }
        .entry-media img { max-width: 100%; height: 200px; object-fit: cover; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .entry-meta { text-align: right; color: #999; }
        .empty-state { text-align: center; padding: 4rem 2rem; color: #999; }
        .sidebar_toggle { display: none; }

        @media (max-width: 768px) {
            .dictionary-grid { grid-template-columns: 1fr; }
            .hero-section h1 { font-size: 2.5rem; }
            .entry-card { padding: 1.5rem; }
        }
    </style>

    <script>
        // Smooth search focus
        document.querySelector('.search-container input').focus();

        // Add to favorites (placeholder)
        document.querySelectorAll('.entry-card').forEach(card => {
            card.addEventListener('click', (e) => {
                if (e.target.tagName !== 'A' && e.target.tagName !== 'BUTTON' && e.target.tagName !== 'AUDIO') {
                    navigator.clipboard.writeText(card.querySelector('h3').textContent);
                    card.style.background = 'rgba(40,167,69,0.1)';
                    setTimeout(() => card.style.background = '', 1000);
                }
            });
        });
    </script>
</body>
</html>
