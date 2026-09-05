<?php
require 'config/constants.php';
session_start();

// Check if user is logged in and not admin
if (!isset($_SESSION['user-id']) || isset($_SESSION['user_is_admin'])) {
    header('location: ' . ROOT_URL . 'signin.php');
    die();
}

// Include database connection
require 'admin/config/database.php';

// Check if table exists, create if not
$table_check_query = "SHOW TABLES LIKE 'fante_history'";
$table_check_result = mysqli_query($connection, $table_check_query);

if (mysqli_num_rows($table_check_result) == 0) {
    // Table doesn't exist, create it
    $create_table_query = "CREATE TABLE IF NOT EXISTS fante_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        details TEXT NOT NULL,
        video VARCHAR(255),
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        user_id INT,
        admin_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    if (!mysqli_query($connection, $create_table_query)) {
        $_SESSION['contribution_error'] = 'Database setup error. Please contact administrator.';
        header('location: ' . ROOT_URL . 'user-history-input.php');
        die();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = mysqli_real_escape_string($connection, $_POST['title']);
    $details = mysqli_real_escape_string($connection, $_POST['details']);
    $user_id = $_SESSION['user-id'];
    $video = '';

    // Handle video upload with security validation
    if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/flv', 'video/webm'];
        $file_type = mime_content_type($_FILES['video']['tmp_name']);
        $file_size = $_FILES['video']['size'];

        if (!in_array($file_type, $allowed_types)) {
            $_SESSION['contribution_error'] = 'Invalid video file type. Only MP4, AVI, MOV, WMV, FLV, and WebM are allowed.';
        } elseif ($file_size > 50 * 1024 * 1024) { // 50MB limit
            $_SESSION['contribution_error'] = 'Video file is too large. Maximum size is 50MB.';
        } else {
            $video_name = time() . '_' . basename($_FILES['video']['name']);
            $video_path = 'images/' . $video_name;
            if (move_uploaded_file($_FILES['video']['tmp_name'], $video_path)) {
                $video = $video_name;
            } else {
                $_SESSION['contribution_error'] = 'Failed to upload video file.';
            }
        }
    }

    // Check if contribution already exists
    $check_query = "SELECT id FROM fante_history WHERE user_id = ? AND title = ? AND status = 'pending'";
    $check_stmt = mysqli_prepare($connection, $check_query);
    mysqli_stmt_bind_param($check_stmt, 'is', $user_id, $title);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        $_SESSION['contribution_error'] = 'You have already submitted this history title and it is pending approval.';
    } else {
        $query = "INSERT INTO fante_history (title, details, video, status, user_id) VALUES (?, ?, ?, 'pending', ?)";
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, 'sssi', $title, $details, $video, $user_id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['contribution_success'] = 'Your history contribution has been submitted and is pending approval.';
        } else {
            $_SESSION['contribution_error'] = 'Failed to submit contribution.';
        }
    }
    mysqli_stmt_close($check_stmt);
    header('location: ' . ROOT_URL . 'user-history-input.php');
    die();
}

// Fetch user's pending contributions
$user_id = $_SESSION['user-id'];
$query = "SELECT * FROM fante_history WHERE user_id = ? AND status = 'pending' ORDER BY created_at DESC";
$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$contributions = mysqli_stmt_get_result($stmt);

include 'partials/header.php';

// Force Light Mode for User History Input Page
echo '<style>
    body.dark-mode {
        --color-bg: #87CEEB !important;
        --color-gray-900: #1e1e66 !important;
        --color-gray-800: #2d2b7c !important;
        --color-gray-700: #4a47a8 !important;
        background: var(--color-bg) !important;
        color: #1e1e66 !important;
    }
    body.dark-mode .container {
        background: transparent !important;
    }
    body.dark-mode nav {
        background: var(--color-primary) !important;
        border-bottom-color: var(--color-bg) !important;
    }
    body.dark-mode .nav__items {
        background: var(--color-bg) !important;
    }
    body.dark-mode h1, body.dark-mode h2, body.dark-mode h3 {
        color: var(--color-blue) !important;
    }
    body.dark-mode p {
        color: #333 !important;
    }
    body.dark-mode .contribution__container {
        background: var(--color-gray-light) !important;
    }
    body.dark-mode .contribution-form-container, 
    body.dark-mode .pending-contributions {
        background: var(--color-white) !important;
    }
    body.dark-mode .form-group label {
        color: #333 !important;
    }
    body.dark-mode .contribution-form input, 
    body.dark-mode .contribution-form textarea {
        background: #fff !important;
        color: #333 !important;
        border-color: #ddd !important;
    }
    body.dark-mode .btn {
        background: linear-gradient(135deg, var(--color-primary), var(--color-primary-variant)) !important;
        color: white !important;
    }
    body.dark-mode .contribution-item {
        background: #f9f9f9 !important;
    }
</style>
<script>
    document.body.classList.remove("dark-mode");
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === "attributes" && mutation.attributeName === "class") {
                document.body.classList.remove("dark-mode");
            }
        });
    });
    observer.observe(document.body, { attributes: true, attributeFilter: ["class"] });
</script>';
?>

<section class="contribution">
    <div class="container contribution__container">
        <h2>Contribute to Fante History</h2>
        <p>Help expand our Fante history database by submitting historical information and videos.</p>

        <?php if (isset($_SESSION['contribution_success'])) : ?>
            <div class="alert__message success">
                <p><?= $_SESSION['contribution_success']; unset($_SESSION['contribution_success']); ?></p>
            </div>
        <?php endif ?>
        <?php if (isset($_SESSION['contribution_error'])) : ?>
            <div class="alert__message error">
                <p><?= $_SESSION['contribution_error']; unset($_SESSION['contribution_error']); ?></p>
            </div>
        <?php endif ?>

        <div class="contribution-form-container">
            <h3>Submit History Entry</h3>
            <form action="" method="POST" enctype="multipart/form-data" class="contribution-form">
                <div class="form-group">
                    <label for="title">History Title *</label>
                    <input type="text" id="title" name="title" placeholder="Enter the history title" required>
                </div>
                <div class="form-group">
                    <label for="details">History Details *</label>
                    <textarea id="details" name="details" placeholder="Provide detailed historical information" required></textarea>
                </div>
                <div class="form-group">
                    <label for="video">Video (Optional)</label>
                    <input type="file" id="video" name="video" accept="video/*">
                    <small>Upload a video related to the history</small>
                </div>
                <button type="submit" class="btn btn-primary">Submit Contribution</button>
            </form>
        </div>

        <div class="pending-contributions">
            <h3>Your Pending Contributions</h3>
            <?php if (mysqli_num_rows($contributions) > 0) : ?>
                <div class="contributions-list">
                    <?php while ($contribution = mysqli_fetch_assoc($contributions)) : ?>
                        <div class="contribution-item">
                            <h4><?= htmlspecialchars($contribution['title']) ?></h4>
                            <p><strong>Details:</strong> <?= htmlspecialchars(substr($contribution['details'], 0, 100)) ?>...</p>
                            <p><strong>Status:</strong> <span class="status pending">Pending Approval</span></p>
                            <small>Submitted on: <?= date('M d, Y', strtotime($contribution['created_at'])) ?></small>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else : ?>
                <p>You have no pending contributions.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
.contribution {
    padding: 4rem 0;
    background: var(--color-bg);
    margin-top: 8rem;
    border-radius: 20px;
    box-shadow: inset 0 0 10px rgba(190, 99, 99, 0.1);
    
}

.contribution__container {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 1rem;
    text-align: center;
    margin-top: 4rem;
    border-radius: var(--card-border-radius, 2rem);
    box-shadow: inset 0 0 10px rgba(190, 99, 99, 0.1);
    background: var(--color-gray-light);
}

.contribution-form-container, .pending-contributions {
    background: var(--color-white);
    padding: 2rem;
    border-radius: var(--card-border-radius, 2rem);
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
    backdrop-filter: blur(10px);
}

.contribution-form .form-group {
    margin-bottom: 1.5rem;
    text-align: left;
    border-radius: var(--card-border-radius, 2rem);
}

.contribution-form label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.contribution-form input, .contribution-form textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
    border-radius: var(--card-border-radius, 2rem);
    box-shadow: inset 0 0 5px rgba(0,0,0,0.05);
    backdrop-filter: blur(10px);
}

.contribution-form textarea {
    resize: vertical;
    min-height: 150px;
    box-shadow: inset 0 0 5px rgba(0,0,0,0.05);
    backdrop-filter: blur(10px);
}

.contribution-form small {
    display: block;
    margin-top: 0.25rem;
    color: #666;
}

.contributions-list {
    display: grid;
    gap: 1rem;
}

.contribution-item {
    background: #f9f9f9;
    padding: 1rem;
    border-radius: 4px;
    border-left: 4px solid var(--color-primary);
    text-align: left;
    box-shadow: inset 0 0 5px rgba(0,0,0,0.05);
    backdrop-filter: blur(10px);
}

.contribution-item h4 {
    margin: 0 0 0.5rem 0;
    color: var(--color-primary);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.contribution-item p {
    margin-bottom: 3rem;
    color: #333;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.5;

}

.status.pending {
    color: orange;
    font-weight: 600;
}
</style>

<?php include 'partials/footer.php'; ?>
