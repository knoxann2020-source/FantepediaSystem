<?php
session_start();
require __DIR__ . '/config/constants.php';
require __DIR__ . '/config/database.php';

if (!isset($_SESSION['user-id'])) {
    header('location: ' . ROOT_URL . 'signin.php');
    die();
}

// Table check
$table_check = "SHOW TABLES LIKE 'fante_music_dance'";
if (mysqli_num_rows(mysqli_query($connection, $table_check)) == 0) {
    // Table created by admin already
}

// Handle submission
if (isset($_POST['submit'])) {
    $category = filter_var($_POST['category'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $user_id = $_SESSION['user-id'];

// Duplicate check
$dup_q = "SELECT id FROM fante_music_dance WHERE title = ? AND category = ? AND user_id = ?";
$dup_stmt = mysqli_prepare($connection, $dup_q);
if ($dup_stmt) {
    mysqli_stmt_bind_param($dup_stmt, 'ssi', $title, $category, $user_id);
    mysqli_stmt_execute($dup_stmt);
    if (mysqli_num_rows(mysqli_stmt_get_result($dup_stmt)) > 0) {
        $_SESSION['md-error'] = 'Duplicate entry. Modify or check existing.';
    } else {
        // Uploads
        $image_name = $audio_name = $video_name = '';
        $upload_dir = __DIR__ . '/images/music-dance/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        foreach (['image' => ['jpg','jpeg','png','gif','webp'], 'audio' => ['mp3','wav','ogg','m4a'], 'video' => ['mp4','webm','avi']] as $type => $allowed) {
            if (isset($_FILES[$type]) && $_FILES[$type]['error'] == 0) {
                $ext = strtolower(pathinfo($_FILES[$type]['name'], PATHINFO_EXTENSION));
                if (in_array($ext, $allowed)) {
                    $file_name = time() . '_' . basename($_FILES[$type]['name']);
                    $dest = $upload_dir . $file_name;
                    if (move_uploaded_file($_FILES[$type]['tmp_name'], $dest)) {
                        $$type_name = 'images/music-dance/' . $file_name;
                    }
                } else {
                    $_SESSION['md-error'] = ucfirst($type) . ' invalid format';
                }
            }
        }

        if (!isset($_SESSION['md-error'])) {
            $insert_q = "INSERT INTO fante_music_dance (category, title, description, image, audio, video, status, user_id) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)";
            $stmt = mysqli_prepare($connection, $insert_q);
            mysqli_stmt_bind_param($stmt, 'ssssssi', $category, $title, $description, $image_name, $audio_name, $video_name, $user_id);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['md-success'] = 'Contribution submitted for review!';
            } else {
                $_SESSION['md-error'] = 'Submission failed';
            }
        }
    }
    }
    header('location: ' . ROOT_URL . 'music-dance-input.php');
    die();
}

// Create table if missing
$table_check = "SHOW TABLES LIKE 'fante_music_dance'";
if (mysqli_num_rows(mysqli_query($connection, $table_check)) == 0) {
    $create_table = "CREATE TABLE fante_music_dance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category ENUM('Music','Dance') NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        image VARCHAR(255) DEFAULT NULL,
        audio VARCHAR(255) DEFAULT NULL,
        video VARCHAR(255) DEFAULT NULL,
        status ENUM('pending','approved','rejected','draft') DEFAULT 'draft',
        user_id INT DEFAULT NULL,
        admin_id INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    mysqli_query($connection, $create_table) or die('Table creation failed');
}

// User's contributions
$user_id = $_SESSION['user-id'];
$user_contribs_q = "SELECT * FROM fante_music_dance WHERE user_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($connection, $user_contribs_q);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $user_contribs = mysqli_stmt_get_result($stmt);
} else {
    $user_contribs = mysqli_query($connection, "SELECT * FROM fante_music_dance WHERE user_id = $user_id ORDER BY created_at DESC") ?: [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music & Dance Contribution - Fantepedia</title>
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Modern form styles from cloth-input.php */
        :root { --primary: #6f6af8; --success: #27ae60; }
        .contribution-container { max-width: 900px; margin: 0 auto; padding: 40px 20px; }
        .page-header { text-align: center; margin-bottom: 40px; }
        .page-header h1 { color: var(--primary); font-size: 2rem; display: flex; align-items: center; justify-content: center; gap: 15px; }
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; display: flex; align-items: center; gap: 12px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .form-card { background: white; border-radius: 15px; padding: 35px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .form-group { margin-bottom: 25px; }
        .form-group label { display: block; margin-bottom: 10px; font-weight: 600; color: var(--primary); }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 14px 18px; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 1rem; transition: all 0.3s; }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(111,106,248,0.1); }
        .form-group textarea { min-height: 150px; resize: vertical; }
        .file-input-wrapper input[type="file"] { position: absolute; opacity: 0; }
        .file-input-label { display: flex; align-items: center; justify-content: center; padding: 25px; border: 2px dashed #e0e0e0; border-radius: 10px; cursor: pointer; transition: all 0.3s; }
        .file-input-label:hover { border-color: var(--primary); background: #f8f9fa; }
        .file-input-label i { font-size: 2rem; color: var(--primary); margin-right: 12px; }
        .submit-btn { width: 100%; padding: 16px; background: linear-gradient(135deg, var(--success), #21867a); color: white; border: none; border-radius: 10px; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(42,157,143,0.4); }
        .contributions-list { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .contribution-item { display: flex; align-items: center; gap: 20px; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px; margin-bottom: 15px; }
        .contribution-item img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
        .status-badge { padding: 6px 14px; border-radius: 20px; font-size: 0.85rem; font-weight: 500; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        @media (max-width: 768px) { .contribution-container { padding: 20px 15px; } .contribution-item { flex-direction: column; text-align: center; } }
    </style>
</head>
<body>
<?php include 'partials/header.php'; ?>

<div class="contribution-container">
    <a href="<?= ROOT_URL ?>music-dance.php" class="back-link" style="display: inline-flex; align-items: center; gap: 8px; color: var(--primary); text-decoration: none; font-weight: 500; margin-bottom: 20px;">
        <i class="fas fa-arrow-left"></i> Back to Music & Dance
    </a>

    <div class="page-header">
        <h1><i class="fas fa-music"></i> Music & Dance Contribution</h1>
        <p>Share Fante music and dance traditions (pending admin approval)</p>
    </div>

    <!-- Messages -->
    <?php if (isset($_SESSION['md-success'])): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $_SESSION['md-success']; unset($_SESSION['md-success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['md-error'])): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $_SESSION['md-error']; unset($_SESSION['md-error']); ?></div>
    <?php endif; ?>

    <!-- Form -->
    <div class="form-card">
        <h2 style="color: var(--primary); margin-bottom: 25px; display: flex; align-items: center; gap: 10px;"><i class="fas fa-plus-circle"></i> Submit Contribution</h2>
        <form action="" method="POST" enctype="multipart/form-data" id="contribForm">
            <div class="form-group">
                <label for="category">Category *</label>
                <select name="category" id="category" required onchange="updateIcon()">
                    <option value="">Select Category</option>
                    <option value="Music">🎵 Music</option>
                    <option value="Dance">💃 Dance</option>
                </select>
            </div>
            <div class="form-group">
                <label for="title">Title *</label>
                <input type="text" id="title" name="title" required placeholder="e.g., Adowa Dance">
            </div>
            <div class="form-group">
                <label for="description">Description *</label>
                <textarea name="description" id="description" required placeholder="Describe the music/dance tradition..."></textarea>
            </div>
            <div class="form-group">
                <label>Image (optional)</label>
                <div class="file-input-wrapper">
                    <div class="file-input-label" id="img-label">
                        <i class="fas fa-image"></i> <span>Upload image</span>
                    </div>
                    <input type="file" name="image" id="image" accept="image/*" onchange="handleFile('image', 'img-label')">
                </div>
            </div>
            <div class="form-group">
                <label>Audio (optional)</label>
                <div class="file-input-wrapper">
                    <div class="file-input-label" id="audio-label">
                        <i class="fas fa-music"></i> <span>Upload audio</span>
                    </div>
                    <input type="file" name="audio" id="audio" accept="audio/*" onchange="handleFile('audio', 'audio-label')">
                </div>
            </div>
            <div class="form-group">
                <label>Video (optional)</label>
                <div class="file-input-wrapper">
                    <div class="file-input-label" id="video-label">
                        <i class="fas fa-video"></i> <span>Upload video</span>
                    </div>
                    <input type="file" name="video" id="video" accept="video/*" onchange="handleFile('video', 'video-label')">
                </div>
            </div>
            <button type="submit" name="submit" class="submit-btn"><i class="fas fa-paper-plane"></i> Submit for Review</button>
        </form>
    </div>

    <!-- User's Contributions -->
    <div class="contributions-list">
        <h2 style="color: var(--primary); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;"><i class="fas fa-history"></i> Your Contributions (<?= mysqli_num_rows($user_contribs) ?>)</h2>
        <?php if (mysqli_num_rows($user_contribs) > 0): ?>
            <?php while ($contrib = mysqli_fetch_assoc($user_contribs)): ?>
                <div class="contribution-item">
                    <?php if ($contrib['image']): ?>
                        <img src="<?= ROOT_URL ?><?= $contrib['image'] ?>" alt="<?= htmlspecialchars($contrib['title']) ?>">
                    <?php endif; ?>
                    <div style="flex: 1;">
                        <div style="font-size: 1.1rem; font-weight: 600; color: var(--primary);"><?= htmlspecialchars($contrib['title']) ?></div>
                        <span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 15px; font-size: 0.85rem; background: <?= $contrib['category']=='Music' ? '#e3f2fd' : '#fce4ec' ?>; color: <?= $contrib['category']=='Music' ? '#1976d2' : '#c2185b' ?>;">
                            <?= $contrib['category']=='Music' ? '🎵' : '💃' ?> <?= htmlspecialchars($contrib['category']) ?>
                        </span>
                        <div style="font-size: 0.85rem; color: #999; margin-top: 8px;"><i class="far fa-calendar-alt"></i> <?= date('M d, Y', strtotime($contrib['created_at'])) ?></div>
                    </div>
                    <span class="status-badge status-<?= $contrib['status'] ?>">
                        <?= $contrib['status']=='pending' ? '<i class="fas fa-clock"></i> Pending' : 
                            ($contrib['status']=='approved' ? '<i class="fas fa-check-circle"></i> Approved' : '<i class="fas fa-times-circle"></i> Rejected') ?>
                    </span>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #999;">
                <i class="fas fa-inbox" style="font-size: 3rem; color: #ddd; margin-bottom: 15px;"></i>
                <p>No contributions yet. Submit your first one above!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function handleFile(id, labelId) {
    const input = document.getElementById(id);
    const label = document.getElementById(labelId);
    if (input.files[0]) {
        label.innerHTML = `<i class="fas fa-check-circle" style="color: #27ae60;"></i> <span>${input.files[0].name}</span>`;
        label.style.borderColor = '#27ae60';
        label.style.background = '#d4edda';
    }
}
function updateIcon() {
    const select = document.getElementById('category');
    // Icon update logic if needed
}
document.getElementById('contribForm').onsubmit = function(e) {
    const title = document.getElementById('title').value.trim();
    const category = document.getElementById('category').value;
    const desc = document.getElementById('description').value.trim();
    if (!title || !category || !desc) {
        e.preventDefault();
        alert('Please fill all required fields.');
        return false;
    }
    const btn = e.target.querySelector('.submit-btn');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    btn.disabled = true;
};
</script>

<?php include 'partials/footer.php'; ?>
</body>
</html>

