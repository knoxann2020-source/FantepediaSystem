<?php
require 'config/constants.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user-id'])) {
    header('location: ' . ROOT_URL . 'signin.php');
    die();
}

// Include database connection
require 'config/database.php';

// Check if table exists, create if not
$table_check_query = "SHOW TABLES LIKE 'pending_contributions'";
$table_check_result = mysqli_query($connection, $table_check_query);

if (mysqli_num_rows($table_check_result) == 0) {
    // Table doesn't exist, create it (without foreign keys first to avoid constraint issues)
    $create_table_query = "CREATE TABLE IF NOT EXISTS pending_contributions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        category_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        excerpt VARCHAR(500),
        images JSON,
        contact_info VARCHAR(255),
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if (!mysqli_query($connection, $create_table_query)) {
        $_SESSION['contribution_error'] = 'Database setup error. Please contact administrator.';
        header('location: ' . ROOT_URL . 'user-contribution.php');
        die();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $word = mysqli_real_escape_string($connection, $_POST['word']);
    $meaning = mysqli_real_escape_string($connection, $_POST['meaning']);
    $origin = mysqli_real_escape_string($connection, $_POST['origin']);
    $user_id = $_SESSION['user-id'];
    $image = '';
    $pronunciation = '';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_name = time() . '_' . $_FILES['image']['name'];
        $image_path = 'images/' . $image_name;
        move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
        $image = $image_name;
    }

    // Handle pronunciation upload
    if (isset($_FILES['pronunciation']) && $_FILES['pronunciation']['error'] === UPLOAD_ERR_OK) {
        $pronunciation_name = time() . '_' . $_FILES['pronunciation']['name'];
        $pronunciation_path = 'images/FanteWords/' . $pronunciation_name;
        move_uploaded_file($_FILES['pronunciation']['tmp_name'], $pronunciation_path);
        $pronunciation = $pronunciation_name;
    }

    // Check if contribution already exists
    $check_query = "SELECT id FROM pending_contributions WHERE user_id = ? AND title = ? AND status = 'pending'";
    $check_stmt = mysqli_prepare($connection, $check_query);
    mysqli_stmt_bind_param($check_stmt, 'is', $user_id, $word);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        $_SESSION['contribution_error'] = 'You have already submitted this word and it is pending approval.';
    } else {
        $query = "INSERT INTO pending_contributions (user_id, category_id, title, content, excerpt, images, contact_info, status) VALUES (?, 1, ?, ?, ?, ?, ?, 'pending')";
        $stmt = mysqli_prepare($connection, $query);
        $images_json = json_encode([$image]);
        mysqli_stmt_bind_param($stmt, 'issssss', $user_id, $word, $meaning, $origin, $images_json, $pronunciation);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['contribution_success'] = 'Your contribution has been submitted and is pending approval.';
        } else {
            $_SESSION['contribution_error'] = 'Failed to submit contribution.';
        }
    }
    mysqli_stmt_close($check_stmt);
    header('location: ' . ROOT_URL . 'user-contribution.php');
    die();
}

// Fetch user's pending contributions
$user_id = $_SESSION['user-id'];
$query = "SELECT * FROM pending_contributions WHERE user_id = ? AND status = 'pending' ORDER BY submitted_at DESC";
$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$contributions = mysqli_stmt_get_result($stmt);

include 'partials/header.php';

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
        <h2>Contribute to Fante Dictionary</h2>
        <p>Help expand our Fante dictionary by submitting new words and their meanings.</p>

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
            <h3>Submit New Word</h3>
            <form action="" method="POST" enctype="multipart/form-data" class="contribution-form">
                <div class="form-group">
                    <label for="word">Word *</label>
                    <input type="text" id="word" name="word" placeholder="Enter the Fante new word" required>
                </div>
                <div class="form-group">
                    <label for="meaning">Meaning *</label>
                    <textarea id="meaning" name="meaning" placeholder="Enter the English meaning" required></textarea>
                </div>
                <div class="form-group">
                    <label for="origin">Origin/Notes</label>
                    <textarea id="origin" name="origin" placeholder="Additional notes or origin of the word"></textarea>
                </div>
                <div class="form-group">
                    <label for="image">Image (Optional)</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <small>Upload an image related to the word</small>
                </div>
                <div class="form-group">
                    <label for="pronunciation">Pronunciation Audio (Optional)</label>
                    <input type="file" id="pronunciation" name="pronunciation" accept="audio/*">
                    <small>Upload an audio file for pronunciation</small>
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
                            <p><strong>Content:</strong> <?= htmlspecialchars(substr($contribution['content'], 0, 100)) ?>...</p>
                            <?php if ($contribution['excerpt']) : ?>
                                <p><strong>Excerpt:</strong> <?= htmlspecialchars($contribution['excerpt']) ?></p>
                            <?php endif; ?>
                            <p><strong>Status:</strong> <span class="status pending">Pending Approval</span></p>
                            <small>Submitted on: <?= date('M d, Y', strtotime($contribution['submitted_at'])) ?></small>
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
    min-height: 100px;
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
    margin: 0.5rem 0;
    

}

.status.pending {
    color: orange;
    font-weight: 600;

}
</style>

<?php include 'partials/footer.php'; ?>
