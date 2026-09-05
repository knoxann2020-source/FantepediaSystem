<?php
session_start();
require 'config/constants.php';
require 'config/database.php';

// Auto-login if remember token exists and is valid
if (!isset($_SESSION['user-id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];

    // Fetch user by remember token
    $query = "SELECT * FROM users WHERE remember_token = ?";
    $stmt = mysqli_prepare($connection, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);

            // Set session for access control
            $_SESSION['user-id'] = $user['id'];

            // Set session if user is an admin
            if ($user['is_admin'] == 1) {
                $_SESSION['user_is_admin'] = true;
            }

            // Redirect to admin or home
            if (isset($_SESSION['user_is_admin']) && $_SESSION['user_is_admin']) {
                header('location: ' . ROOT_URL . 'admin/');
            } else {
header('location: ' . ROOT_URL . 'user-dashboard.php');
            }
            die();
        }
        mysqli_stmt_close($stmt);
    }
}

// get back form data if there is a signin error
$username_email = $_SESSION['signin-data']['username_email'] ?? NULL;
$password = $_SESSION['signin-data']['password'] ?? NULL;

// Display success message from signup if it exists
if(isset($_SESSION['signup-success'])) {
    $success_message = $_SESSION['signup-success'];
    unset($_SESSION['signup-success']);
} else {
    $success_message = NULL;
}

// Display SMS notification alert if it exists
$sms_notification = $_SESSION['sms_notification'] ?? NULL;
unset($_SESSION['sms_notification']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE-edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Fantepedia System</title>

    <!-- Favicon -->
<link rel="icon" type="image/jpeg" href="<?= ROOT_URL ?>images/3warriors.jpg">

    <!-- Styles -->
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/style.css">
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/auth-hero-styles.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js"></script>

    <!-- OAuth SDKs -->\n    <!-- Google Platform JS -->\n    <script src="https://accounts.google.com/gsi/client" async defer></script>\n    <!-- Facebook SDK -->\n    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v20.0&appId=<?= FB_APP_ID ?>"></script>\n    \n    <!-- Auth JS -->\n    <script src="<?= ROOT_URL ?>js/auth.js" defer></script>\n</head>
<body>
    <?php include 'partials/header.php'; ?>

    <main class="auth-main">
        <section class="auth-container">
<div class="auth-panel auth-left">
                <div class="welcome-content">
                    <img src="<?= ROOT_URL ?>images/3warriors.jpg" alt="3 Warriors | Fante Heritage" class="auth-hero-img">
                    <h2>Welcome Back
                    <p>Discover the rich heritage of Fante culture, language, history, and traditions with Fantepedia.</p>
                    <ul class="contact-details">
                        <li><i class="fas fa-envelope"></i> info@fantepedia.com</li>
                        <li><i class="fas fa-phone"></i> +233 543 67 2521</li>
                        <li><i class="fas fa-globe"></i> fantepedia.com</li>
                    </ul>
                    <small class="copyright">&copy; <?= date('Y') ?> Fantepedia System. All rights reserved.</small>
                </div>
            </div>

            <div class="auth-panel auth-right">
                <div class="form-section">
                    <h2>Sign In</h2>

                    <?php if(isset($_SESSION['signin'])): ?>
                        <div class="alert__message error">
                            <p><?= $_SESSION['signin']; unset($_SESSION['signin']); ?></p>
                        </div>
                    <?php elseif(isset($success_message)): ?>
                        <div class="alert__message success">
                            <p><?= $success_message; ?></p>
                        </div>
                    <?php elseif(isset($sms_notification)): ?>
                        <div class="alert__message <?= $sms_notification['type'] === 'warning' ? 'warning' : 'info' ?>">
                            <p><i class="fas fa-sms"></i> <?= htmlspecialchars($sms_notification['message']) ?></p>
                        </div>
                    <?php endif; ?>

                    <form action="<?= ROOT_URL ?>signin-logic.php" class="auth-form" enctype="multipart/form-data" method="POST">
<div class="form-group">
                            <label for="username_email">Username or Email</label>
                            <input type="text" id="username_email" name="username_email" value="<?= $username_email ?>" placeholder="Username or Email" required>
                        </div>

<div class="form-group password-container">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" value="<?= $password ?>" placeholder="Password" required>
                            <i class="fas fa-eye"></i>
                        </div>

<div class="form-group checkbox-group">
                            <label class="remember-me-container">
                                <input type="checkbox" name="remember_me" id="remember_me">
                                <span>Remember Me</span>
                            </label>
                        </div>

                        <div class="form-group checkbox-group terms-container">
                            <label class="terms-checkbox">
                                <input type="checkbox" name="terms" required>
                                <span>I agree to the <a href="<?= ROOT_URL ?>terms-and-conditions.php" class="terms-link" target="_blank">Terms & Conditions</a></span>
                            </label>
                        </div>

                        <div class="form-group recaptcha-container">
                            <div class="g-recaptcha" data-sitekey="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"></div>
                        </div>

                        <button type="submit" name="submit" class="btn">Sign In</button>
                    </form>

                    <div class="divider">
                        <span>or continue with</span>
                    </div>

                    <div class="social-options">
                        <button class="social-login-btn" data-provider="google">
                            <i class="fab fa-google"></i> Google
                        </button>
                        <button class="social-login-btn" data-provider="facebook">
                            <i class="fab fa-facebook-f"></i> Facebook
                        </button>
                    </div>

                    <div class="form-links">
                        <small>Not having an account? <a href="<?= ROOT_URL ?>signup.php">Sign Up</a></small>
                        <small><a href="<?= ROOT_URL ?>forgot-password.php">Forgot Password?</a></small>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Terms Modal -->
    <div id="terms-modal" class="modal">
        <div class="modal-content">
            <span class="terms-close">&times;</span>
            <h3>Terms & Conditions</h3>
            <p>Preview of <a href="<?= ROOT_URL ?>terms-and-conditions.php" target="_blank">full Terms & Conditions</a>. Key points:</p>
            <ul>
                <li>Must be 13+ years old</li>
                <li>Accurate information required</li>
                <li>No spam/abusive/illegal content</li>
                <li>Respect Fante cultural guidelines</li>
            </ul>
            <p class="modal-note">Full terms apply. <a href="<?= ROOT_URL ?>terms-and-conditions.php" target="_blank">Read complete version</a>.</p>
        </div>
    </div>

    <?php include 'partials/footer.php'; ?>
</body>
</html>

