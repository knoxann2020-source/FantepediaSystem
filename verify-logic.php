<?php
session_start();
require 'config/database.php';
require 'config/constants.php';
require 'includes/email-service.php';
require 'includes/sms-service.php';

if (isset($_GET['token'])) {
    $token_hash = hash('sha256', $_GET['token']);
    $stmt = mysqli_prepare($connection, 'SELECT id, firstname, username, email, phone, verification_code_hash FROM users WHERE verification_token_hash = ? AND is_verified = 0 AND verification_expires_at > NOW() LIMIT 1');
    mysqli_stmt_bind_param($stmt, 's', $token_hash);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
} elseif (isset($_POST['verify'])) {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $code = trim($_POST['verification_code'] ?? '');

    if (!$email || !preg_match('/^[0-9]{6}$/', $code)) {
        $_SESSION['verification-error'] = 'Enter the six-digit verification code from your notification.';
        header('Location: ' . ROOT_URL . 'verify.php?email=' . urlencode((string) $email));
        exit;
    }

    $stmt = mysqli_prepare($connection, 'SELECT id, firstname, username, email, phone, verification_code_hash FROM users WHERE email = ? AND is_verified = 0 AND verification_expires_at > NOW() LIMIT 1');
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$user || !password_verify($code, $user['verification_code_hash'])) {
        $_SESSION['verification-error'] = 'The verification code is invalid or has expired.';
        header('Location: ' . ROOT_URL . 'verify.php?email=' . urlencode($email));
        exit;
    }
} else {
    header('Location: ' . ROOT_URL . 'signup.php');
    exit;
}

if (!$user) {
    $_SESSION['verification-error'] = 'The verification link or code is invalid or has expired.';
    header('Location: ' . ROOT_URL . 'signup.php');
    exit;
}

$update = mysqli_prepare($connection, 'UPDATE users SET is_verified = 1, verified_at = NOW(), verification_token_hash = NULL, verification_code_hash = NULL, verification_expires_at = NULL WHERE id = ? AND is_verified = 0');
mysqli_stmt_bind_param($update, 'i', $user['id']);
$updated = mysqli_stmt_execute($update);
mysqli_stmt_close($update);

if (!$updated) {
    $_SESSION['verification-error'] = 'Account confirmation failed. Please try again.';
    header('Location: ' . ROOT_URL . 'verify.php?email=' . urlencode($email));
    exit;
}

sendAccountCredentialsEmail($user['email'], $user['firstname'], $user['username']);
if (!empty($user['phone'])) {
    sendSMS($user['phone'], "Your Fantepedia account is confirmed. Username: {$user['username']}. Sign in at " . ROOT_URL . 'signin.php');
}
$_SESSION['signup-success'] = 'Your account is confirmed. Your username and sign-in details have been sent to your email.';
unset($_SESSION['verification_email']);
header('Location: ' . ROOT_URL . 'signin.php');
exit;
