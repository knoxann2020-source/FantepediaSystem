<?php
session_start();
require 'config/constants.php';

$email = filter_var($_GET['email'] ?? $_SESSION['verification_email'] ?? '', FILTER_VALIDATE_EMAIL) ?: '';
$message = $_SESSION['verification-message'] ?? null;
unset($_SESSION['verification-message']);
$error = $_SESSION['verification-error'] ?? null;
unset($_SESSION['verification-error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Account - Fantepedia System</title>
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/style.css">
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/auth-hero-styles.css">
</head>
<body>
    <?php include 'partials/header.php'; ?>
    <main class="auth-main">
        <section class="auth-container">
            <div class="auth-panel auth-right">
                <div class="form-section">
                    <h2>Confirm your account</h2>
                    <?php if ($message): ?><div class="alert__message success"><p><?= htmlspecialchars($message) ?></p></div><?php endif; ?>
                    <?php if ($error): ?><div class="alert__message error"><p><?= htmlspecialchars($error) ?></p></div><?php endif; ?>
                    <p>Enter the six-digit code sent to your email or mobile number.</p>
                    <form action="<?= ROOT_URL ?>verify-logic.php" class="auth-form" method="POST">
                        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                        <div class="form-group">
                            <label for="verification_code">Verification code</label>
                            <input type="text" id="verification_code" name="verification_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required>
                        </div>
                        <button type="submit" name="verify" class="btn">Confirm account</button>
                    </form>
                    <p><a href="<?= ROOT_URL ?>signup.php">Return to signup</a></p>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
