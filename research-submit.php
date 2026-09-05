<?php
session_start();
require 'config/constants.php';
require 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user-id'])) {
    header('location: ' . ROOT_URL . 'signin.php');
    die();
}

// Get current user info
$user_id = $_SESSION['user-id'];

// Check if table exists, create if not
$table_check_query = "SHOW TABLES LIKE 'research_contributions'";
$table_check_result = mysqli_query($connection, $table_check_query);

if (mysqli_num_rows($table_check_result) == 0) {
    // Create research_contributions table
    $create_table_query = "CREATE TABLE IF NOT EXISTS research_contributions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        content_type ENUM('text', 'graph', 'audio', 'video', 'mixed') NOT NULL,
        file_path VARCHAR(500),
        file_type VARCHAR(100),
        file_name VARCHAR(255),
        content TEXT,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        reviewed_at TIMESTAMP NULL,
        reviewed_by INT NULL,
        admin_notes TEXT
    )";
    
    if (!mysqli_query($connection, $create_table_query)) {
        $_SESSION['research_error'] = 'Database setup error. Please contact administrator.';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_research'])) {
    $title = mysqli_real_escape_string($connection, $_POST['title']);
    $description = mysqli_real_escape_string($connection, $_POST['description']);
    $content_type = mysqli_real_escape_string($connection, $_POST['content_type']);
    $content = mysqli_real_escape_string($connection, $_POST['content']);
    
    $uploaded_file = '';
    $file_type = '';
    $file_name = '';
    
    // Handle file upload
    if (isset($_FILES['research_file']) && $_FILES['research_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES['research_file']['tmp_name'];
        $file_name = $_FILES['research_file']['name'];
        $file_size = $_FILES['research_file']['size'];
        $file_type = $_FILES['research_file']['type'];
        
        // Get file extension
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Allowed extensions
        $allowed_extensions = ['pdf', 'doc', 'docx', 'txt', 'png', 'jpg', 'jpeg', 'gif', 'mp3', 'wav', 'ogg', 'mp4', 'avi', 'mov', 'webm'];
        
        if (in_array($file_ext, $allowed_extensions)) {
            // Create upload directory if not exists
            $upload_dir = 'images/research/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate unique filename
            $new_file_name = time() . '_' . preg_replace('/[^A-Za-z0-9_-]/', '', $file_name);
            $upload_path = $upload_dir . $new_file_name;
            
            if (move_uploaded_file($file_tmp_name, $upload_path)) {
                $uploaded_file = $new_file_name;
            } else {
                $_SESSION['research_error'] = 'Failed to upload file. Please try again.';
            }
        } else {
            $_SESSION['research_error'] = 'Invalid file type. Allowed: PDF, DOC, DOCX, TXT, Images (PNG, JPG, GIF), Audio (MP3, WAV, OGG), Video (MP4, AVI, MOV, WEBM)';
        }
    }
    
    // Check if research already exists (duplicate check)
    $check_query = "SELECT id FROM research_contributions 
                    WHERE user_id = ? 
                    AND title = ? 
                    AND (status = 'pending' OR status = 'approved')";
    $check_stmt = mysqli_prepare($connection, $check_query);
    mysqli_stmt_bind_param($check_stmt, 'is', $user_id, $title);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);
    
    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        $_SESSION['research_error'] = 'This research work has already been submitted and is pending approval or already approved.';
        echo '<script>alert("This research work has already been submitted!");</script>';
    } else {
        // Insert new research contribution
        $insert_query = "INSERT INTO research_contributions 
                        (user_id, title, description, content_type, file_path, file_type, file_name, content, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = mysqli_prepare($connection, $insert_query);
        $status = 'pending';
        mysqli_stmt_bind_param($insert_stmt, 'issssssss', 
            $user_id, $title, $description, $content_type, 
            $uploaded_file, $file_type, $file_name, $content, $status);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            $_SESSION['research_success'] = 'Your research work has been submitted successfully and is pending admin approval.';
            echo '<script>alert("Research submitted successfully! Pending admin approval.");</script>';
        } else {
            $_SESSION['research_error'] = 'Failed to submit research. Please try again.';
        }
        mysqli_stmt_close($insert_stmt);
    }
    mysqli_stmt_close($check_stmt);
}

// Fetch user's submitted research
$user_research_query = "SELECT * FROM research_contributions WHERE user_id = ? ORDER BY submitted_at DESC";
$user_research_stmt = mysqli_prepare($connection, $user_research_query);
mysqli_stmt_bind_param($user_research_stmt, 'i', $user_id);
mysqli_stmt_execute($user_research_stmt);
$user_research = mysqli_stmt_get_result($user_research_stmt);

// Fetch pending research for admin (if admin)
$is_admin = isset($_SESSION['user_is_admin']) && $_SESSION['user_is_admin'];
if ($is_admin) {
    $pending_research_query = "SELECT r.*, u.username, u.firstname, u.lastname 
                              FROM research_contributions r 
                              LEFT JOIN users u ON r.user_id = u.id 
                              WHERE r.status = 'pending' 
                              ORDER BY r.submitted_at DESC";
    $pending_research_result = mysqli_query($connection, $pending_research_query);
    $pending_research = mysqli_fetch_all($pending_research_result, MYSQLI_ASSOC);
}

include 'partials/header.php';
?>

<style>
/* Research Submit Page Styles */

.research-submit-hero {
    background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-purple) 100%);
    padding: 4rem 2rem;
    margin-top: 6rem;
    border-radius: var(--card-border-radius-5);
    text-align: center;
    color: white;
    position: relative;
    overflow: hidden;
    margin-bottom: 5rem;
}

.research-submit-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text y="50%" font-size="50">📝</text></svg>') repeat;
    opacity: 0.1;
    pointer-events: none;
}

.research-submit-hero h1 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    color: white;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    position: relative;
    z-index: 1;
}

.research-submit-hero p {
    font-size: 1.2rem;
    opacity: 0.9;
    max-width: 600px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}

/* Form Container */
.research-submit-container {
    max-width: 900px;
    margin: -3rem auto 2rem;
    padding: 2rem;
    background: var(--color-gray-900);
    border-radius: var(--card-border-radius-4);
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    position: relative;
    z-index: 2;
    border: 3px solid var(--color-primary);
    margin-bottom: 5rem;
}

/* Alert Messages */
.alert-container {
    margin-bottom: 1.5rem;
}

.alert-message {
    padding: 1rem 1.5rem;
    border-radius: var(--card-border-radius-3);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.alert-message.success {
    background: linear-gradient(135deg, #00c476 0%, #00a368 100%);
    color: white;
    border-left: 4px solid #00ff88;
}

.alert-message.error {
    background: linear-gradient(135deg, #da0f3f 0%, #b80d35 100%);
    color: white;
    border-left: 4px solid #ff4466;
}

.alert-message.info {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    border-left: 4px solid #3399ff;
}

.alert-message i {
    font-size: 1.5rem;
}

/* Form Styles */
.research-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    border: 5px solid var(--color-bg);
    border-radius: var(--card-border-radius-4);
    padding: 2rem;
    background: var(--color-gray-800);
    box-shadow: 4 8px 30px rgba(0,0,0,0.3);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    font-weight: 600;
    color: var(--color-white);
    font-size: 0.95rem;
}

.form-group label .required {
    color: var(--color-red);
}

.form-group input,
.form-group textarea,
.form-group select {
    padding: 0.8rem 1rem;
    border: 2px solid var(--color-gray-700);
    border-radius: var(--card-border-radius-3);
    background: var(--color-gray-700);
    color: white;
    font-size: 1rem;
    transition: var(--transition);
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 15px rgba(111, 106, 248, 0.3);
}

.form-group textarea {
    min-height: 150px;
    resize: vertical;
}

.form-group small {
    color: var(--color-gray-200);
    font-size: 0.85rem;
}

/* File Upload */
.file-upload-wrapper {
    position: relative;
    border: 2px dashed var(--color-gray-700);
    border-radius: var(--card-border-radius-3);
    padding: 2rem;
    text-align: center;
    transition: var(--transition);
    cursor: pointer;
}

.file-upload-wrapper:hover {
    border-color: var(--color-primary);
    background: rgba(111, 106, 248, 0.1);
}

.file-upload-wrapper.dragover {
    border-color: var(--color-primary);
    background: rgba(111, 106, 248, 0.2);
}

.file-upload-wrapper input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.file-upload-icon {
    font-size: 3rem;
    color: var(--color-primary);
    margin-bottom: 1rem;
}

.file-upload-text {
    color: var(--color-gray-200);
    font-size: 1rem;
}

.file-upload-hint {
    color: var(--color-gray-300);
    font-size: 0.85rem;
    margin-top: 0.5rem;
}

/* Content Type Selection */
.content-type-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 1rem;
}

@media (max-width: 768px) {
    .content-type-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.content-type-option {
    position: relative;
}

.content-type-option input[type="radio"] {
    position: absolute;
    opacity: 0;
    cursor: pointer;
}

.content-type-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1.5rem 1rem;
    background: var(--color-gray-700);
    border: 2px solid var(--color-gray-700);
    border-radius: var(--card-border-radius-3);
    cursor: pointer;
    transition: var(--transition);
    text-align: center;
}

.content-type-card:hover {
    border-color: var(--color-primary);
    transform: translateY(-3px);
}

.content-type-option input[type="radio"]:checked + .content-type-card {
    border-color: var(--color-primary);
    background: linear-gradient(135deg, rgba(111, 106, 248, 0.2) 0%, rgba(146, 30, 255, 0.2) 100%);
    box-shadow: 0 0 20px rgba(111, 106, 248, 0.3);
}

.content-type-card i {
    font-size: 2rem;
    color: var(--color-primary);
    margin-bottom: 0.5rem;
}

.content-type-card span {
    color: var(--color-white);
    font-size: 0.9rem;
    font-weight: 500;
}

/* Submit Button */
.submit-btn {
    padding: 1rem 2rem;
    background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-purple) 100%);
    color: white;
    border: none;
    border-radius: var(--card-border-radius-3);
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 1rem;
}

.submit-btn:hover {
    transform: translateY(-3px);
    box-shadow: 3 8px 25px rgba(146, 182, 17, 0.4);
}

.submit-btn:active {
    transform: translateY(0);
}

/* Tabs */
.research-tabs {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin: 2rem 0;
    flex-wrap: wrap;
}

.research-tab {
    padding: 1rem 2rem;
    background: var(--color-gray-900);
    color: white;
    border: none;
    border-radius: var(--card-border-radius-3);
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
}

.research-tab:hover {
    background: var(--color-gray-700);
}

.research-tab.active {
    background: var(--color-primary);
    box-shadow: 0 4px 15px rgba(111, 106, 248, 0.4);
}

/* My Submissions */
.my-submissions {
    margin-top: 2rem;
}

.submission-card {
    background: var(--color-gray-900);
    border-radius: var(--card-border-radius-3);
    padding: 1.5rem;
    margin-bottom: 1rem;
    border-left: 4px solid var(--color-primary);
    transition: var(--transition);
}

.submission-card:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.submission-card.pending {
    border-left-color: orange;
}

.submission-card.approved {
    border-left-color: var(--color-green);
}

.submission-card.rejected {
    border-left-color: var(--color-red);
}

.submission-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.submission-title {
    font-size: 1.2rem;
    color: var(--color-primary);
    font-weight: 600;
}

.submission-status {
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.submission-status.pending {
    background: rgba(255, 193, 7, 0.2);
    color: #ffc107;
}

.submission-status.approved {
    background: rgba(0, 196, 118, 0.2);
    color: #00c476;
}

.submission-status.rejected {
    background: rgba(218, 15, 63, 0.2);
    color: #da0f3f;
}

.submission-meta {
    display: flex;
    gap: 2rem;
    color: var(--color-gray-200);
    font-size: 0.9rem;
    flex-wrap: wrap;
}

.submission-meta span {
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

/* Admin Alert Section */
.admin-alert-section {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
    border-radius: var(--card-border-radius-3);
    padding: 2rem;
    margin: 2rem 0;
    color: white;
}

.admin-alert-section h3 {
    color: white;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.pending-item {
    background: rgba(255,255,255,0.1);
    border-radius: var(--card-border-radius-2);
    padding: 1rem;
    margin-bottom: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.pending-info h4 {
    color: white;
    margin-bottom: 0.3rem;
}

.pending-info p {
    font-size: 0.9rem;
    opacity: 0.9;
}

.pending-actions {
    display: flex;
    gap: 0.5rem;
}

.pending-actions .btn {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
}

.pending-actions .btn-approve {
    background: var(--color-green);
}

.pending-actions .btn-reject {
    background: var(--color-red);
}

/* No Submissions */
.no-submissions {
    text-align: center;
    padding: 3rem;
    color: var(--color-gray-200);
}

.no-submissions i {
    font-size: 4rem;
    color: var(--color-gray-700);
    margin-bottom: 1rem;
}

/* Loading Animation */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    display: none;
}

.loading-overlay.active {
    display: flex;
}

.loading-spinner {
    width: 60px;
    height: 60px;
    border: 4px solid var(--color-gray-700);
    border-top: 4px solid var(--color-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Guidelines Section */
.guidelines-section {
    background: var(--color-gray-900);
    border-radius: var(--card-border-radius-4);
    padding: 2rem;
    margin: 2rem 0;
    border: 5px solid var(--color-bg);
}

.guidelines-section h3 {
    color: var(--color-primary);
    margin-bottom: 1rem;
}

.guidelines-list {
    list-style: none;
    padding: 0;
}

.guidelines-list li {
    padding: 0.5rem 0;
    color: var(--color-gray-200);
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
}

.guidelines-list li i {
    color: var(--color-primary);
    margin-top: 0.3rem;
}

.my-submissions {
    margin-top: 2rem;
    border: 3px solid var(--color-primary);
    border-radius: var(--card-border-radius-4);
    padding: 2rem;
    box-shadow: 5px 10px 30px rgba(0,0,0,0.3);

}

.my-submissions h2 {
    color: var(--color-primary);
    margin-bottom: 1.5rem;
}
.my-submissions .submission-card {
    border-left-width: 4px;
    border-left-style: solid;
    border-left-color: var(--color-gray-700);
}

.my-submissions .submission-card.pending {
    border-left-color: orange;
}

.my-submissions .submission-card.approved {
    border-left-color: var(--color-green);
}
.my-submissions .submission-card.rejected {
    border-left-color: var(--color-red);
}


/* Progress Indicator */
.progress-indicator {
    display: flex;
    justify-content: space-between;
    margin-bottom: 2rem;
    position: relative;
}

.progress-indicator::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 2px;
    background: var(--color-gray-700);
    z-index: 0;
    transform: translateY(-50%);
}

.progress-step {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.progress-step-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--color-gray-700);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-gray-200);
    transition: var(--transition);
}

.progress-step.active .progress-step-icon {
    background: var(--color-primary);
    color: white;
    box-shadow: 0 0 15px rgba(111, 106, 248, 0.5);
}

.progress-step-label {
    font-size: 0.8rem;
    color: var(--color-gray-200);
}

.progress-step.active .progress-step-label {
    color: var(--color-primary);
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeInUp 0.5s ease forwards;
}

.animate-delay-1 { animation-delay: 0.1s; }
.animate-delay-2 { animation-delay: 0.2s; }
.animate-delay-3 { animation-delay: 0.3s; }
.animate-delay-4 { animation-delay: 0.4s; }
</style>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner"></div>
</div>

<!-- Hero Section -->
<section class="research-submit-hero">
    <h1>📝 Submit Your Research</h1>
    <p>Share your valuable research on Fante culture and heritage with our community</p>
</section>

<!-- Main Content -->
<div class="container">
    <!-- Alert Messages -->
    <div class="research-submit-container section extra-margin animate-fade-in">
        <?php if (isset($_SESSION['research_success'])): ?>
            <div class="alert-container">
                <div class="alert-message success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $_SESSION['research_success']; unset($_SESSION['research_success']); ?></span>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['research_error'])): ?>
            <div class="alert-container">
                <div class="alert-message error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $_SESSION['research_error']; unset($_SESSION['research_error']); ?></span>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Admin Alert Section -->
        <?php if ($is_admin && !empty($pending_research)): ?>
            <div class="admin-alert-section animate-fade-in">
                <h3><i class="fas fa-bell"></i> Pending Research Contributions</h3>
                <p>There are <?php echo count($pending_research); ?> research work(s) waiting for your review.</p>
                
                <?php foreach ($pending_research as $pending): ?>
                    <div class="pending-item">
                        <div class="pending-info">
                            <h4><?php echo htmlspecialchars($pending['title']); ?></h4>
                            <p>Submitted by: <?php echo htmlspecialchars($pending['firstname'] . ' ' . $pending['lastname']); ?> (@<?php echo htmlspecialchars($pending['username']); ?>)</p>
                            <p>Type: <?php echo ucfirst(htmlspecialchars($pending['content_type'])); ?> | Submitted: <?php echo date('M d, Y', strtotime($pending['submitted_at'])); ?></p>
                        </div>
                        <div class="pending-actions">
                            <a href="admin/approve-research.php?id=<?php echo $pending['id']; ?>" class="btn pending-actions btn-approve" onclick="return confirm('Approve this research?')">
                                <i class="fas fa-check"></i> Approve
                            </a>
                            <a href="admin/reject-research.php?id=<?php echo $pending['id']; ?>" class="btn pending-actions btn-reject" onclick="return confirm('Reject this research?')">
                                <i class="fas fa-times"></i> Reject
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- Progress Indicator -->
        <div class="progress-indicator">
            <div class="progress-step active">
                <div class="progress-step-icon"><i class="fas fa-pen"></i></div>
                <span class="progress-step-label">Submit</span>
            </div>
            <div class="progress-step">
                <div class="progress-step-icon"><i class="fas fa-clock"></i></div>
                <span class="progress-step-label">Pending</span>
            </div>
            <div class="progress-step">
                <div class="progress-step-icon"><i class="fas fa-check"></i></div>
                <span class="progress-step-label">Review</span>
            </div>
            <div class="progress-step">
                <div class="progress-step-icon"><i class="fas fa-star"></i></div>
                <span class="progress-step-label">Published</span>
            </div>
        </div>
        
        <!-- Tabs -->
        <div class="research-tabs">
            <button class="research-tab active" data-tab="submit">Submit Research</button>
            <button class="research-tab" data-tab="submissions">My Submissions</button>
            <button class="research-tab" data-tab="guidelines">Guidelines</button>
        </div>
        
        <!-- Submit Form Tab -->
        <div id="submit" class="research-content active ">
            <form action="" method="POST" enctype="multipart/form-data" class="research-form" id="researchForm">
                <div class="form-row animate-fade-in animate-delay-1">
                    <div class="form-group">
                        <label for="title">Research Title <span class="required">*</span></label>
                        <input type="text" id="title" name="title" placeholder="Enter your research title" required>
                    </div>
                    <div class="form-group">
                        <label for="content_type">Content Type <span class="required">*</span></label>
                        <select id="content_type" name="content_type" required>
                            <option value="">Select content type</option>
                            <option value="text">📄 Text Document</option>
                            <option value="graph">📊 Graph/Chart</option>
                            <option value="audio">🎵 Audio</option>
                            <option value="video">🎬 Video</option>
                            <option value="mixed">📁 Mixed/Multimedia</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group animate-fade-in animate-delay-2">
                    <label for="description">Brief Description <span class="required">*</span></label>
                    <textarea id="description" name="description" placeholder="Provide a brief summary of your research work (100-200 words)" required></textarea>
                </div>
                
                <div class="form-group animate-fade-in animate-delay-3">
                    <label for="content">Detailed Content</label>
                    <textarea id="content" name="content" placeholder="Enter the full content of your research here..."></textarea>
                </div>
                
                <div class="form-group animate-fade-in animate-delay-4">
                    <label>Upload Research File (Optional)</label>
                    <div class="file-upload-wrapper" id="fileUploadWrapper">
                        <input type="file" id="research_file" name="research_file" accept=".pdf,.doc,.docx,.txt,.png,.jpg,.jpeg,.gif,.mp3,.wav,.ogg,.mp4,.avi,.mov,.webm">
                        <div class="file-upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <div class="file-upload-text">
                            Drag & drop your file here or <span style="color: var(--color-primary);">browse</span>
                        </div>
                        <div class="file-upload-hint">
                            Supported: PDF, DOC, DOCX, TXT, Images (PNG, JPG, GIF), Audio (MP3, WAV, OGG), Video (MP4, AVI, MOV, WEBM)<br>
                            Max file size: 50MB
                        </div>
                    </div>
                    <div id="fileName" style="margin-top: 0.5rem; color: var(--color-primary); display: none;">
                        <i class="fas fa-file"></i> <span id="selectedFileName"></span>
                    </div>
                </div>
                
                <button type="submit" name="submit_research" class="submit-btn" onclick="showLoading()">
                    <i class="fas fa-paper-plane"></i>
                    Submit Research for Review
                </button>
            </form>
        </div>
        
        <!-- My Submissions Tab -->
        <div id="submissions" class="research-content">
            <div class="my-submissions">
                <h2 style="color: var(--color-primary); margin-bottom: 1.5rem;">My Research Submissions</h2>
                
                <?php if (mysqli_num_rows($user_research) > 0): ?>
                    <?php while ($research = mysqli_fetch_assoc($user_research)): ?>
                        <div class="submission-card <?php echo $research['status']; ?>">
                            <div class="submission-header">
                                <h3 class="submission-title"><?php echo htmlspecialchars($research['title']); ?></h3>
                                <span class="submission-status <?php echo $research['status']; ?>">
                                    <?php echo ucfirst($research['status']); ?>
                                </span>
                            </div>
                            <p style="color: var(--color-gray-200); margin-bottom: 1rem;">
                                <?php echo htmlspecialchars(substr($research['description'], 0, 200)); ?>...
                            </p>
                            <div class="submission-meta">
                                <span><i class="fas fa-folder"></i> <?php echo ucfirst($research['content_type']); ?></span>
                                <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($research['submitted_at'])); ?></span>
                                <?php if ($research['file_name']): ?>
                                    <span><i class="fas fa-file"></i> <?php echo htmlspecialchars($research['file_name']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-submissions">
                        <i class="fas fa-inbox"></i>
                        <h3>No Submissions Yet</h3>
                        <p>You haven't submitted any research work yet. Click on "Submit Research" to add your first contribution.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Guidelines Tab -->
        <div id="guidelines" class="research-content">
            <div class="guidelines-section">
                <h3><i class="fas fa-book"></i> Submission Guidelines</h3>
                <ul class="guidelines-list">
                    <li><i class="fas fa-check"></i> Ensure your research is original and properly cited</li>
                    <li><i class="fas fa-check"></i> All submissions are subject to admin approval before publication</li>
                    <li><i class="fas fa-check"></i> Supported file formats: PDF, DOC, DOCX, TXT, Images (PNG, JPG, GIF), Audio (MP3, WAV, OGG), Video (MP4, AVI, MOV, WEBM)</li>
                    <li><i class="fas fa-check"></i> Maximum file size: 50MB</li>
                    <li><i class="fas fa-check"></i> Provide accurate and detailed descriptions of your research</li>
                    <li><i class="fas fa-check"></i> Ensure all content follows our community guidelines</li>
                    <li><i class="fas fa-check"></i> You can track the status of your submissions in "My Submissions" tab</li>
                    <li><i class="fas fa-check"></i> Duplicate submissions will be rejected automatically</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching functionality
    const tabs = document.querySelectorAll('.research-tab');
    const contents = document.querySelectorAll('.research-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs and contents
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding content
            this.classList.add('active');
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // File upload visual feedback
    const fileInput = document.getElementById('research_file');
    const fileUploadWrapper = document.getElementById('fileUploadWrapper');
    const fileNameDisplay = document.getElementById('fileName');
    const selectedFileName = document.getElementById('selectedFileName');
    
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                selectedFileName.textContent = this.files[0].name;
                fileNameDisplay.style.display = 'block';
                fileUploadWrapper.style.borderColor = 'var(--color-primary)';
                fileUploadWrapper.style.background = 'rgba(111, 106, 248, 0.1)';
            }
        });
        
        // Drag and drop effects
        fileUploadWrapper.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });
        
        fileUploadWrapper.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });
        
        fileUploadWrapper.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                fileInput.files = e.dataTransfer.files;
                selectedFileName.textContent = e.dataTransfer.files[0].name;
                fileNameDisplay.style.display = 'block';
                this.style.borderColor = 'var(--color-primary)';
            }
        });
    }
    
    // Form validation
    const researchForm = document.getElementById('researchForm');
    if (researchForm) {
        researchForm.addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const description = document.getElementById('description').value.trim();
            const contentType = document.getElementById('content_type').value;
            
            if (!title || !description || !contentType) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }
            
            if (description.length < 50) {
                e.preventDefault();
                alert('Please provide a more detailed description (at least 50 characters).');
                return false;
            }
            
            showLoading();
        });
    }
});

// Show loading overlay
function showLoading() {
    const loadingOverlay = document.getElementById('loadingOverlay');
    if (loadingOverlay) {
        loadingOverlay.classList.add('active');
    }
}

// Hide loading overlay
function hideLoading() {
    const loadingOverlay = document.getElementById('loadingOverlay');
    if (loadingOverlay) {
        loadingOverlay.classList.remove('active');
    }
}

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert-message');
    alerts.forEach(function(alert) {
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-10px)';
        alert.style.transition = 'all 0.3s ease';
        setTimeout(function() {
            alert.remove();
        }, 300);
    });
}, 5000);
</script>

<?php
include 'partials/footer.php';
?>
