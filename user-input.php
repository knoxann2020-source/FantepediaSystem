<?php
session_start();
include 'partials/header.php';

// Database connection
require 'admin/config/database.php';

// Force Light Mode for User Contribution Page
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
    body.dark-mode section {
        background: rgba(255,255,255,0.05) !important;
    }
    body.dark-mode .contribution-form {
        background: rgba(187, 194, 204, 0.9) !important;
    }
    body.dark-mode .form-group label {
        color: #333 !important;
    }
    body.dark-mode .form-group input, 
    body.dark-mode .form-group textarea, 
    body.dark-mode .form-group select {
        background: #fff !important;
        color: #333 !important;
        border-color: #ddd !important;
    }
    body.dark-mode .btn {
        background: linear-gradient(135deg, var(--color-primary), var(--color-primary-variant)) !important;
        color: white !important;
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contribution'])) {
    if (!isset($_SESSION['user-id'])) {
        header('location: signin.php');
        die();
    }

    $category = mysqli_real_escape_string($connection, $_POST['category']);
    $title = mysqli_real_escape_string($connection, $_POST['title']);
    $description = mysqli_real_escape_string($connection, $_POST['description']);
    $video = '';
    $audio = '';

    // Handle video upload
    if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $video_name = time() . '_' . $_FILES['video']['name'];
        $video_path = 'images/' . $video_name;
        move_uploaded_file($_FILES['video']['tmp_name'], $video_path);
        $video = $video_name;
    }

    // Handle audio upload
    if (isset($_FILES['audio']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) {
        $audio_name = time() . '_' . $_FILES['audio']['name'];
        $audio_path = 'images/' . $audio_name;
        move_uploaded_file($_FILES['audio']['tmp_name'], $audio_path);
        $audio = $audio_name;
    }

    $query = "INSERT INTO fante_phonetics (category, title, description, video, audio, status, user_id) VALUES (?, ?, ?, ?, ?, 'pending', ?)";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, 'sssssi', $category, $title, $description, $video, $audio, $_SESSION['user-id']);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = 'Your contribution has been submitted for review. Thank you!';
    } else {
        $_SESSION['error'] = 'Failed to submit contribution. Please try again.';
    }

    header('location: user-input.php');
    die();
}
?>

<section class="user-input section__extra-margin">
    <div class="container">
        <h1>Contribute to Fante Phonetics</h1>
        <p>Help us build a comprehensive collection of Fante language resources. 
            Submit videos, audio, or descriptions for review by our administrators.</p>

        <?php if (isset($_SESSION['success'])) : ?>
            <div class="alert__message success">
                <p><?= $_SESSION['success']; unset($_SESSION['success']); ?></p>
            </div>
        <?php endif ?>
        <?php if (isset($_SESSION['error'])) : ?>
            <div class="alert__message error">
                <p><?= $_SESSION['error']; unset($_SESSION['error']); ?></p>
            </div>
        <?php endif ?>

        <?php if (!isset($_SESSION['user-id'])): ?>
            <div class="alert__message error">
                <p>You must be logged in to contribute. <a href="signin.php">Sign In</a> or <a href="signup.php">Sign Up</a></p>
            </div>
        <?php else: ?>
            <form action="" method="POST" enctype="multipart/form-data" class="contribution-form">
                <div class="form-group">
                    <label for="category">Category *</label>
                    <select id="category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="Fante Alphabets">Fante Alphabets</option>
                        <option value="Akan Proverbs">Akan Proverbs</option>
                        <option value="Fante Numbers">Fante Numbers</option>
                        <option value="Names of Months">Names of Months</option>
                        <option value="Names of Objects">Names of Objects</option>
                        <option value="Days of the Week">Days of the Week</option>
                        <option value="Names of Animals">Names of Animals</option>
                        <option value="Regions in Ghana">Regions in Ghana</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="title">Title *</label>
                    <input type="text" id="title" name="title" required placeholder="Enter a descriptive title">
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Provide additional details or context"></textarea>
                </div>
                <div class="form-group">
                    <label for="video">Video (Optional)</label>
                    <input type="file" id="video" name="video" accept="video/*">
                    <small>Upload a video file (MP4, AVI, etc.)</small>
                </div>
                <div class="form-group">
                    <label for="audio">Audio (Optional)</label>
                    <input type="file" id="audio" name="audio" accept="audio/*">
                    <small>Upload an audio file (MP3, WAV, etc.)</small>
                </div>
                <button type="submit" name="submit_contribution" class="btn">Submit Contribution</button>
            </form>
        <?php endif; ?>
    </div>
</section>

<style>
.contribution-form {
    max-width: 600px;
    margin: 0 auto;
    background: rgba(187, 194, 204, 0.9);
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.user-input .container {
    align-items: center;
    box-shadow: 0 40px 60px rgba(0,0,0,0.1);
    border-radius: 20px;
    background: rgba(156, 156, 224, 0.9);
    backdrop-filter: blur(250px);
    padding: 2rem;

}

.user-input h1 {
    text-align: center;
    margin-bottom: 1rem;
    color: var(--color-primary);
}

.user-input p {
    text-align: center;
    margin-bottom: 2rem;
    color: #333;
}

.form-group {
    margin-bottom: 1.5rem;
    border-radius: var(--card-border-radius, 10px);
    box-shadow: inset 0 2px 4px rgba(73, 18, 161, 0.73);


}
.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: bold;
}
.form-group input, .form-group textarea, .form-group select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 10px;
    font-size: 1rem;
    font-family: inherit;
    box-sizing: border-box;
}
.form-group textarea {
    resize: vertical;
    min-height: 100px;
}
.form-group small {
    display: block;
    margin-top: 0.25rem;
    color: #666;
    font-size: 0.9rem;
}
.btn {
    background: linear-gradient(135deg, var(--color-primary), var(--color-primary-variant));
    color: white;
    border: none;
    padding: 0.75rem 2rem;
    border-radius: 10px;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
}
.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}
</style>

<?php include 'partials/footer.php'; ?>
