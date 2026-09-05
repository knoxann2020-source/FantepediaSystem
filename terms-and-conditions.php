<?php
session_start();
require 'config/constants.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Conditions - Fantepedia System</title>
    <link rel="icon" type="image/svg+xml" href="<?= ROOT_URL ?>images/default-avatar.svg">
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/style.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <main>
        <section class="terms-section container">
            <div class="terms-header">
                <h1>Terms & Conditions</h1>
                <p class="terms-subtitle">Last Updated: <?= date('F d, Y') ?></p>
            </div>

            <div class="terms-content">
                <article class="terms-article">
                    <h2>1. Acceptance of Terms</h2>
                    <p>By accessing or using Fantepedia System ("Platform"), you agree to be bound by these Terms & Conditions ("Terms"). If you do not agree, please do not use the Platform.</p>
                </article>

                <article class="terms-article">
                    <h2>2. User Accounts</h2>
                    <ul>
                        <li>You must be at least 13 years old to create an account.</li>
                        <li>Provide accurate, current information during registration.</li>
                        <li>You are responsible for maintaining confidentiality of your account and password.</li>
                        <li>Notify us immediately of unauthorized use.</li>
                    </ul>
                </article>

                <article class="terms-article">
                    <h2>3. User Contributions</h2>
                    <ul>
                        <li>You retain ownership of your content but grant us a worldwide, non-exclusive, royalty-free license to use, display, and distribute.</li>
                        <li>Content must respect Fante cultural heritage; no offensive, illegal, or harmful material.</li>
                        <li>We reserve right to review, edit, or remove contributions.</li>
                        <li>Contributions may be used for educational/promotional purposes.</li>
                    </ul>
                </article>

                <article class="terms-article">
                    <h2>4. Prohibited Conduct</h2>
                    <ul>
                        <li>No spam, harassment, hate speech, or illegal activities.</li>
                        <li>No uploading viruses/malware or infringing third-party rights.</li>
                        <li>No commercial use without permission.</li>
                        <li>Respect intellectual property and cultural sensitivities.</li>
                    </ul>
                </article>

                <article class="terms-article">
                    <h2>5. Privacy & Data</h2>
                    <p>Your data is protected per our <a href="<?= ROOT_URL ?>privacy-policy.php">Privacy Policy</a>. We collect minimal data for functionality; no selling/sharing without consent.</p>
                </article>

                <article class="terms-article">
                    <h2>6. Intellectual Property</h2>
                    <p>Fantepedia content (texts, images, audio) is protected. Personal use allowed; commercial reproduction prohibited without permission.</p>
                </article>

                <article class="terms-article">
                    <h2>7. Disclaimers & Limitation of Liability</h2>
                    <ul>
                        <li>Platform provided "as is"; no warranties for accuracy/completeness.</li>
                        <li>Not liable for user content or third-party links.</li>
                        <li>Liability limited to direct damages, max account fees paid (if any).</li>
                    </ul>
                </article>

                <article class="terms-article">
                    <h2>8. Termination</h2>
                    <p>We may suspend/terminate accounts for violations. You may delete your account anytime.</p>
                </article>

                <article class="terms-article">
                    <h2>9. Governing Law</h2>
                    <p>These Terms governed by laws of Ghana. Disputes resolved in Ghanaian courts.</p>
                </article>

                <article class="terms-article">
                    <h2>10. Changes to Terms</h2>
                    <p>We may update Terms; continued use constitutes acceptance. Check periodically.</p>
                </article>

                <article class="terms-article">
                    <h2>11. Contact</h2>
                    <p>Questions? Email <a href="mailto:info@fantepedia.com">info@fantepedia.com</a></p>
                </article>
            </div>

            <div class="terms-footer">
                <button onclick="window.print()" class="btn">Print Terms</button>
                <a href="<?= ROOT_URL ?>index.php" class="btn btn-secondary">Back to Home</a>
            </div>
        </div>
    </main>

    <?php include 'partials/footer.php'; ?>

    <script src="<?= ROOT_URL ?>js/main.js" defer></script>
    <style>
        .terms-section { max-width: 900px; margin: 2rem auto; padding: 2rem; }
        .terms-header h1 { font-size: 2.5rem; margin-bottom: 0.5rem; }
        .terms-subtitle { color: #666; font-size: 1.1rem; margin-bottom: 3rem; }
        .terms-article { margin-bottom: 2.5rem; }
        .terms-article h2 { color: #2c3e50; font-size: 1.5rem; margin-bottom: 1rem; border-bottom: 2px solid #3498db; padding-bottom: 0.5rem; }
        .terms-article ul { padding-left: 1.5rem; }
        .terms-article li { margin-bottom: 0.5rem; line-height: 1.6; }
        .terms-footer { display: flex; gap: 1rem; justify-content: center; margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #eee; }
        @media (max-width: 768px) { .terms-section { padding: 1rem; } .terms-footer { flex-direction: column; } }
    </style>
</body>
</html>
?>

