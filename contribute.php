<?php
require 'config/constants.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user-id'])) {
    header('location: ' . ROOT_URL . 'signin.php');
    die();
}

include 'partials/header.php';

// Get back form data if there is a contribution error
$title = $_SESSION['contribute-data']['title'] ?? NULL;
$category_id = $_SESSION['contribute-data']['category_id'] ?? NULL;
$content = $_SESSION['contribute-data']['content'] ?? NULL;
$excerpt = $_SESSION['contribute-data']['excerpt'] ?? NULL;
$contact_info = $_SESSION['contribute-data']['contact_info'] ?? NULL;
?>

<script src="https://www.google.com/recaptcha/api.js"></script>

    <!-- TinyMCE Rich Text Editor -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#content',
            height: 400,
            menubar: false,
            plugins: 'lists link image code',
            toolbar: 'bold italic underline | bullist numlist | link image | code',
            content_style: 'body { font-family: Montserrat, sans-serif; font-size: 14px }'
        });
    </script>

<style>
        .contribution-form {
            background: var(--color-white);
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin: 2rem 0;
            bottom-margin: 8rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--color-gray-900);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--color-gray-300);
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--color-primary);
        }

        .file-input-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-input-wrapper input[type="file"] {
            opacity: 0;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            border: 2px dashed var(--color-gray-300);
            border-radius: 4px;
            background: var(--color-gray-50);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-input-label:hover {
            border-color: var(--color-primary);
            background: var(--color-primary-light);
        }

        .file-input-label i {
            margin-right: 0.5rem;
            color: var(--color-primary);
        }

        .image-preview {
            display: none;
            margin-top: 1rem;
            max-width: 200px;
            border-radius: 4px;
        }

        .progress-bar {
            width: 100%;
            height: 4px;
            background: var(--color-gray-200);
            border-radius: 2px;
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .progress-fill {
            height: 100%;
            background: var(--color-primary);
            width: 0%;
            transition: width 0.3s ease;
        }

        .contribution-tips {
            background: var(--color-info-light);
            border: 1px solid var(--color-info);
            border-radius: 4px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .contribution-tips h4 {
            color: var(--color-info-dark);
            margin-bottom: 0.5rem;
        }

        .contribution-tips ul {
            margin: 0;
            padding-left: 1.5rem;
        }

        .contribution-tips li {
            margin-bottom: 0.25rem;
            color: var(--color-gray-700);
        }
    </style>
    <style>
        .contribute-page {
            --contribute-ink: #17324d;
            --contribute-muted: #627387;
            --contribute-border: #d9e2eb;
            --contribute-accent: #d06b3c;
            background: linear-gradient(135deg, #f5f8fb 0%, #eef5f2 100%);
            padding: clamp(2rem, 5vw, 5rem) 1rem;
        }

        .contribute-shell {
            max-width: 1120px;
            margin: 0 auto;
        }

        .contribute-intro {
            max-width: 720px;
            margin-bottom: 1.75rem;
        }

        .contribute-intro h1 {
            color: var(--contribute-ink);
            font-size: clamp(2rem, 4vw, 3.25rem);
            line-height: 1.08;
            margin-bottom: .75rem;
        }

        .contribute-intro p {
            color: var(--contribute-muted);
            font-size: 1.05rem;
            line-height: 1.7;
            margin: 0;
        }

        .contribute-layout {
            display: grid;
            grid-template-columns: minmax(220px, .72fr) minmax(0, 1.7fr);
            gap: 1.5rem;
            align-items: start;
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid var(--contribute-border);
            background: rgba(255, 255, 255, .94);
            box-shadow: 0 16px 40px rgba(23, 50, 77, .08);
            bottom-margin: 8rem;
        }

        .contribution-tips,
        .contribution-form {
            box-sizing: border-box;
            min-width: 0;
            margin: 0;
            border: 1px solid var(--contribute-border);
            border-radius: 12px;
            background: rgba(255, 255, 255, .94);
            box-shadow: 0 16px 40px rgba(23, 50, 77, .08);
        }

        .contribution-tips { padding: 1.5rem; }
        .contribution-tips h4 { margin-top: 0; }
        .contribution-tips li { line-height: 1.5; }
        .contribution-form { padding: clamp(1.25rem, 3vw, 2rem); }

        .contribution-fields {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0 1rem;
        }

        .contribution-fields .form-group:nth-child(3),
        .contribution-fields .form-group:nth-child(5),
        .contribution-fields .form-group:nth-child(6),
        .contribution-fields .form-group:nth-child(7) { grid-column: 1 / -1; }

        .form-group { min-width: 0; margin-bottom: 1.25rem; }
        .form-group input,
        .form-group select,
        .form-group textarea { box-sizing: border-box; max-width: 100%; }
        .form-group textarea { min-height: 220px; resize: vertical; }
        .file-input-label { gap: .6rem; text-align: center; line-height: 1.4; }
        .image-preview { display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: .5rem; max-width: 100%; }
        .image-preview img { width: 100%; height: 80px; object-fit: cover; margin: 0 !important; }
        .g-recaptcha { max-width: 100%; overflow: hidden; }
        .contribution-form { overflow: visible; }
        .contribution-form .btn[type="submit"] {
            display: flex;
            grid-column: 1 / -1;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            width: 100%;
            min-height: 3.25rem;
            box-sizing: border-box;
            padding: .85rem 1.25rem;
            border: 0;
            color: #fff;
            background: var(--color-primary, #d06b3c);
            font: inherit;
            font-weight: 700;
            line-height: 1.3;
            white-space: normal;
            text-align: center;
            visibility: visible;
            opacity: 1;
        }

        .contribution-form .btn[type="submit"]:hover,
        .contribution-form .btn[type="submit"]:focus-visible {
            background: #ae4f29;
            color: #fff;
        }

        .contribution-form .btn[type="submit"]:disabled {
            cursor: wait;
            opacity: .75;
        }

        @media (max-width: 760px) {
            .contribute-layout,
            .contribution-fields { grid-template-columns: 1fr; }
            .contribution-fields .form-group:nth-child(3),
            .contribution-fields .form-group:nth-child(5),
            .contribution-fields .form-group:nth-child(6),
            .contribution-fields .form-group:nth-child(7) { grid-column: auto; }
            .contribution-form .btn[type="submit"] { grid-column: auto; }
        }
    </style>
    <section class="form__section contribute-page">
        <div class="container form__section-container">
            <div class="contribute-shell">
                <div class="contribute-intro">
                    <h1>Contribute to Fantepedia</h1>
                    <p>Share your knowledge about Fante culture, heritage, and traditions. Your contribution will be reviewed by our team before being published.</p>
                </div>

            <?php if(isset($_SESSION['contribute'])): ?>
                <div class="alert__message error">
                    <p>
                        <?= htmlspecialchars($_SESSION['contribute'], ENT_QUOTES, 'UTF-8');
                        unset($_SESSION['contribute']);
                        ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['contribute-success'])): ?>
                <div class="alert__message success">
                    <p>
                        <?= htmlspecialchars($_SESSION['contribute-success'], ENT_QUOTES, 'UTF-8');
                        unset($_SESSION['contribute-success']);
                        ?>
                    </p>
                </div>
            <?php endif; ?>

            <div class="contribute-layout">
            <div class="contribution-tips">
                <h4><i class="fas fa-lightbulb"></i> Contribution Guidelines</h4>
                <ul>
                    <li>Ensure your information is accurate and respectful of Fante culture</li>
                    <li>Provide sources or references when possible</li>
                    <li>Use clear, descriptive titles</li>
                    <li>Include relevant images or media (optional)</li>
                    <li>Keep content appropriate and educational</li>
                </ul>
            </div>

            <form action="<?= ROOT_URL ?>contribute-logic.php" method="POST" enctype="multipart/form-data" class="contribution-form">
                <div class="contribution-fields">
                <div class="form-group">
                    <label for="title">Title *</label>
                    <input type="text" id="title" name="title" value="<?= htmlspecialchars($title ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Enter a descriptive title for your contribution" required>
                </div>

                <div class="form-group">
                    <label for="category_id">Category *</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Select a category</option>
                        <option value="1" <?= $category_id == '1' ? 'selected' : '' ?>>Fante Alphabets</option>
                        <option value="2" <?= $category_id == '2' ? 'selected' : '' ?>>Fante Phonetics</option>
                        <option value="3" <?= $category_id == '3' ? 'selected' : '' ?>>Language Tutorial</option>
                        <option value="4" <?= $category_id == '4' ? 'selected' : '' ?>>Fante History</option>
                        <option value="5" <?= $category_id == '5' ? 'selected' : '' ?>>Fante Panapoly</option>
                        <option value="6" <?= $category_id == '6' ? 'selected' : '' ?>>Fante States</option>
                        <option value="7" <?= $category_id == '7' ? 'selected' : '' ?>>Virtual Museum</option>
                        <option value="8" <?= $category_id == '8' ? 'selected' : '' ?>>Fante Ceremonies</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="content">Content *</label>
                    <textarea id="content" name="content" placeholder="Share your knowledge about Fante culture..." required><?= htmlspecialchars($content ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="excerpt">Excerpt/Summary</label>
                    <input type="text" id="excerpt" name="excerpt" value="<?= htmlspecialchars($excerpt ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Brief summary of your contribution (optional)">
                </div>

                <div class="form-group">
                    <label for="images">Images (Optional)</label>
                    <div class="file-input-wrapper">
                        <input type="file" id="images" name="images[]" accept="image/*" multiple>
                        <div class="file-input-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Click to upload images or drag and drop</span>
                        </div>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                    <div id="imagePreview" class="image-preview"></div>
                </div>

                <div class="form-group">
                    <label for="contact_info">Contact Information</label>
                    <input type="text" id="contact_info" name="contact_info" value="<?= htmlspecialchars($contact_info ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Email or phone for follow-up questions (optional)">
                </div>

                <div class="form-group">
                    <div class="g-recaptcha" data-sitekey="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"></div>
                </div>

                <button type="submit" name="submit" class="btn">
                    <i class="fas fa-paper-plane"></i> Submit Contribution
                </button>
                </div>
            </form>
            </div>
            </div>
        
        </div>
    </section>

    <script>
        // Image upload preview and progress
        const fileInput = document.getElementById('images');
        const imagePreview = document.getElementById('imagePreview');
        const progressFill = document.getElementById('progressFill');
        const fileInputLabel = document.querySelector('.file-input-label');

        fileInput.addEventListener('change', function(e) {
            const files = e.target.files;
            imagePreview.innerHTML = '';
            imagePreview.style.display = 'none';

            if (files.length > 0) {
                imagePreview.style.display = 'block';
                fileInputLabel.innerHTML = `<i class="fas fa-check-circle"></i> ${files.length} file(s) selected`;

                Array.from(files).forEach(file => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.style.maxWidth = '150px';
                            img.style.margin = '5px';
                            img.style.borderRadius = '4px';
                            imagePreview.appendChild(img);
                        };
                        reader.readAsDataURL(file);
                    }
                });

                // Simulate progress
                let progress = 0;
                const interval = setInterval(() => {
                    progress += 10;
                    progressFill.style.width = progress + '%';
                    if (progress >= 100) {
                        clearInterval(interval);
                    }
                }, 100);
            } else {
                fileInputLabel.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> <span>Click to upload images or drag and drop</span>';
                progressFill.style.width = '0%';
            }
        });

        // Drag and drop functionality
        const fileInputWrapper = document.querySelector('.file-input-wrapper');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileInputWrapper.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            fileInputWrapper.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            fileInputWrapper.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            fileInputWrapper.classList.add('dragover');
        }

        function unhighlight(e) {
            fileInputWrapper.classList.remove('dragover');
        }

        fileInputWrapper.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            fileInput.files = files;
            fileInput.dispatchEvent(new Event('change'));
        }

        // Form validation
        document.querySelector('.contribution-form').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const category = document.getElementById('category_id').value;
            const editor = tinymce.get('content');
            const content = editor ? editor.getContent().trim() : document.getElementById('content').value.trim();

            if (!title) {
                alert('Please enter a title for your contribution.');
                e.preventDefault();
                return false;
            }

            if (!category) {
                alert('Please select a category.');
                e.preventDefault();
                return false;
            }

            if (!content) {
                alert('Please enter the content of your contribution.');
                e.preventDefault();
                return false;
            }

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            submitBtn.disabled = true;
        });
    </script>

    <?php include 'partials/footer.php'; ?>
