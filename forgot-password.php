<?php
session_start();
require 'config/constants.php';

// Display success message if reset email was sent
if(isset($_SESSION['forgot-success'])) {
    $success_message = $_SESSION['forgot-success'];
    unset($_SESSION['forgot-success']);
} else {
    $success_message = NULL;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE-edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Fantepedia System</title>

    <!=================FAVICON=================>
    <link rel="icon" type="image/svg+xml" href="./images/default-avatar.svg">

    <!================CUSTOM STYLESHEET=========== >
    <link rel="stylesheet" href="<?= ROOT_URL?>./css/style.css">

    <!=================ICONSCOUT===============>
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <!..==================================FORM SECTION============================================>

   <section class="form__section">
    <div class="container form__section-container">
        <h2>Forgot Password</h2>
        <?php if(isset($_SESSION['forgot-error'])): ?>
            <div class="alert__message error">
                <p>
                    <?= $_SESSION['forgot-error'];
                    unset($_SESSION['forgot-error']);
                    ?>
                </p>
            </div>
        <?php elseif(isset($success_message)): ?>
            <div class="alert__message success">
                <p>
                    <?= $success_message;
                    ?>
                </p>
            </div>
        <?php endif; ?>
        <form action="<?= ROOT_URL ?>forgot-password-logic.php" method="POST">
            <input type="email" name="email" placeholder="Enter your email address" required>

            <button type="submit" name="submit" class="btn">Send Reset Link</button>
        </form>
        <small><p>Remember your password? <a href="signin.php">Sign In</a></p></small>
        </div>
   </section>
    <!..==================================END OF FORM SECTION============================================>
</body>
</html>
