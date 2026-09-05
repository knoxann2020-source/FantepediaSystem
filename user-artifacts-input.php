<?php
session_start();
include 'partials/header.php';

// Check if user is logged in
if (!isset($_SESSION['user-id'])) {
    header('location: ' . ROOT_URL . 'signin.php');
    die();
}

// Database connection
require 'admin/config/database.php';

// Force Light Mode for User Artifacts Input Page
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
    body.dark-mode .form__section-container {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    }
    body.dark-mode .form__control input, 
    body.dark-mode .form__control textarea {
        background: rgba(255,255,255,0.9) !important;
        color: #333 !important;
    }
    body.dark-mode .btn {
        background: linear-gradient(45deg, #ff6b6b, #ee5a24) !important;
        color: white !important;
    }
    body.dark-mode .contribution-card {
        background: white !important;
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
if (isset($_POST['submit'])) {
    $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $user_id = $_SESSION['user-id'];

    // Handle image upload
    $image_name = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = $_FILES['image'];
        $image_name = time() . '_' . $image['name'];
        $image_tmp_name = $image['tmp_name'];
        $image_destination_path = 'images/' . $image_name;

        // Check file type
        $allowed_files = ['png', 'jpg', 'jpeg', 'gif'];
        $extension = explode('.', $image_name);
        $extension = end($extension);
        if (in_array($extension, $allowed_files)) {
            if (move_uploaded_file($image_tmp_name, $image_destination_path)) {
                // Image uploaded successfully
            } else {
                $_SESSION['add-artifact'] = "Failed to upload image";
            }
        } else {
            $_SESSION['add-artifact'] = "File should be png, jpg, jpeg, or gif";
        }
    }

    // Handle video upload (optional)
    $video_name = '';
    if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
        $video = $_FILES['video'];
        $video_name = time() . '_' . $video['name'];
        $video_tmp_name = $video['tmp_name'];
        $video_destination_path = 'images/' . $video_name;

        // Check file type
        $allowed_videos = ['mp4', 'avi', 'mov', 'wmv'];
        $extension = explode('.', $video_name);
        $extension = end($extension);
        if (in_array($extension, $allowed_videos)) {
            if (move_uploaded_file($video_tmp_name, $video_destination_path)) {
                // Video uploaded successfully
            } else {
                $_SESSION['add-artifact'] = "Failed to upload video";
            }
        } else {
            $_SESSION['add-artifact'] = "Video should be mp4, avi, mov, or wmv";
        }
    }

    if (!isset($_SESSION['add-artifact'])) {
        // Insert into database with pending status
        $insert_query = "INSERT INTO fante_artifacts (title, image, description, video, status, user_id) VALUES ('$title', '$image_name', '$description', '$video_name', 'pending', $user_id)";
        $insert_result = mysqli_query($connection, $insert_query);

        if ($insert_result) {
            $_SESSION['add-artifact-success'] = "Artifact submitted successfully. It will be reviewed by an administrator.";
            header('location: ' . ROOT_URL . 'user-artifacts-input.php');
            die();
        } else {
            $_SESSION['add-artifact'] = "Failed to submit artifact";
        }
    }
}

// Fetch user's pending artifacts
$user_id = $_SESSION['user-id'];
$user_artifacts_query = "SELECT * FROM fante_artifacts WHERE user_id = $user_id ORDER BY created_at DESC";
$user_artifacts = mysqli_query($connection, $user_artifacts_query);
?>

<section class="form__section">
    <div class="container form__section-container">
        <h2>Contribute Fante Artifact</h2>
        <p>Help preserve Fante cultural heritage by contributing artifact information. Your submission will be reviewed by administrators before being published.</p>
        <?php if (isset($_SESSION['add-artifact'])) : ?>
            <div class="alert__message error">
                <p>
                    <?= $_SESSION['add-artifact'];
                    unset($_SESSION['add-artifact']);
                    ?>
                </p>
            </div>
        <?php elseif (isset($_SESSION['add-artifact-success'])) : ?>
            <div class="alert__message success">
                <p>
                    <?= $_SESSION['add-artifact-success'];
                    unset($_SESSION['add-artifact-success']);
                    ?>
                </p>
            </div>
        <?php endif ?>
        <form action="<?= ROOT_URL ?>user-artifacts-input.php" method="POST" enctype="multipart/form-data" id="artifact-form">
            <input type="text" name="title" placeholder="Artifact Title" required>
            <textarea rows="10" name="description" placeholder="Artifact Description" required></textarea>
            <div class="form__control">
                <label for="image">Select Image</label>
                <input type="file" name="image" id="image" accept="image/*" required>
                <div id="image-preview" class="file-preview"></div>
            </div>
            <div class="form__control">
                <label for="video">Select Video (Optional)</label>
                <input type="file" name="video" id="video" accept="video/*">
                <div id="video-preview" class="file-preview"></div>
            </div>
            <button type="submit" name="submit" class="btn">Submit Artifact</button>
        </form>
    </div>
</section>

<section class="user-contributions">
    <div class="container">
        <h2>Your Artifact Contributions</h2>
        <?php if ($user_artifacts && mysqli_num_rows($user_artifacts) > 0) : ?>
            <div class="contributions-grid">
                <?php while ($artifact = mysqli_fetch_assoc($user_artifacts)) : ?>
                    <div class="contribution-card">
                        <div class="contribution-header">
                            <h3><?= htmlspecialchars($artifact['title']) ?></h3>
                            <span class="status status-<?= $artifact['status'] ?>"><?= ucfirst($artifact['status']) ?></span>
                        </div>
                        <?php if ($artifact['image']) : ?>
                            <img src="images/<?= $artifact['image'] ?>" alt="Artifact Image" class="contribution-image">
                        <?php endif; ?>
                        <p class="contribution-description"><?= htmlspecialchars(substr($artifact['description'], 0, 150)) ?>...</p>
                        <small>Submitted on: <?= date("M d, Y", strtotime($artifact['created_at'])) ?></small>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else : ?>
            <div class="alert__message info">
                <p>You haven't submitted any artifacts yet. Start contributing above!</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
.form__section-container {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.container__form__section-container {
    max-width: 600px;
    margin: 0 auto;
    border-radius: 20px;
    padding: 2rem;
    background: rgba(255,255,255,0.1);
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    backdrop-filter: blur(10px);
}
.alert__message {
    padding: 1rem;
    border-radius: 10px;
    margin-bottom: 1.5rem;
    font-weight: 600;
}
.alert__message.error {
    background-color: #f8d7da;
    color: #721c24;
    border-color: #f5c6cb;
}
.alert__message.success {
    background-color: #d4edda;
    color: #155724;
    border-color: #c3e6cb;
}
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
    border-radius: 20px;
    background: rgba(193, 223, 154, 0.4);
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    backdrop-filter: blur(10px);
    

}


.form__section-container h2 {
    text-align: center;
    margin-bottom: 1rem;
}

.form__section-container p {
    text-align: center;
    margin-bottom: 2rem;
    opacity: 0.9;
}

.form__control {
    margin-bottom: 1.5rem;
}

.form__control label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.form__control input, .form__control textarea {
    width: 100%;
    padding: 1rem;
    border: none;
    border-radius: 10px;
    background: rgba(255,255,255,0.9);
    color: #333;
    font-size: 1rem;
}

.form__control textarea {
    resize: vertical;
    min-height: 150px;
}

.btn {
    background: linear-gradient(45deg, #ff6b6b, #ee5a24);
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 50px;
    font-size: 1.1rem;
    cursor: pointer;
    transition: transform 0.3s ease;
    display: block;
    width: 100%;
    max-width: 200px;
    margin: 0 auto;
    margin-bottom: 2rem;
}

.btn:hover {
    transform: scale(1.05);
}

.file-preview {
    margin-top: 1rem;
    max-width: 200px;
}

.file-preview img, .file-preview video {
    width: 100%;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.user-contributions {
    margin-top: 3rem;
}

.contributions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.contribution-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.contribution-card:hover {
    transform: translateY(-5px);
}

.contribution-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.contribution-header h3 {
    margin: 0;
    color: #333;
}

.status {
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    color: white;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-approved {
    background-color: #28a745;
}

.status-pending {
    background-color: #ffc107;
    color: black;
}

.status-rejected {
    background-color: #dc3545;
}

.contribution-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 10px;
    margin-bottom: 1rem;
}

.contribution-description {
    color: #666;
    margin-bottom: 1rem;
    line-height: 1.5;
}

.alert__message.info {
    background-color: #d1ecf1;
    color: #0c5460;
    border-color: #bee5eb;
}
</style>

<script>
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('image-preview');
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Image Preview">`;
        };
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '';
    }
});

document.getElementById('video').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('video-preview');
    if (file) {
        const url = URL.createObjectURL(file);
        preview.innerHTML = `<video src="${url}" controls style="max-width: 100%;"></video>`;
    } else {
        preview.innerHTML = '';
    }
});

// Form validation
document.getElementById('artifact-form').addEventListener('submit', function(e) {
    const title = document.querySelector('input[name="title"]').value.trim();
    const description = document.querySelector('textarea[name="description"]').value.trim();
    const image = document.getElementById('image').files[0];

    if (!title || !description || !image) {
        e.preventDefault();
        alert('Please fill in all required fields.');
        return false;
    }

    // Show loading state
    const submitBtn = document.querySelector('button[type="submit"]');
    submitBtn.textContent = 'Submitting...';
    submitBtn.disabled = true;
});
</script>

<?php
include 'partials/footer.php';
?>
