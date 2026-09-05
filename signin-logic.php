<?php
session_start();
require 'config/database.php';

// get signin form data if signin button is clicked
if (isset($_POST['submit'])) {
    // Verify reCAPTCHA
    $recaptcha_response = $_POST['g-recaptcha-response'];
    $recaptcha_secret = RECAPTCHA_SECRET_KEY;
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_data = array(
        'secret' => $recaptcha_secret,
        'response' => $recaptcha_response
    );

    $recaptcha_options = array(
        'http' => array(
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($recaptcha_data)
        )
    );

    $recaptcha_context = stream_context_create($recaptcha_options);
    $recaptcha_result = file_get_contents($recaptcha_url, false, $recaptcha_context);
    $recaptcha_json = json_decode($recaptcha_result);

    if (!$recaptcha_json->success) {
        $_SESSION['signin'] = "reCAPTCHA verification failed. Please try again.";
    } else {
        $username_email = filter_var($_POST['username_email'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $password = filter_var($_POST['password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (!$username_email) {
            $_SESSION['signin'] = "Username or Email required";
        } elseif (!$password) {
            $_SESSION['signin'] = "Password required";
        } elseif (empty($_POST['terms'])) {
            $_SESSION['signin'] = "You must agree to Terms & Conditions";
        } else {
            // Check if user exists
            $query = "SELECT * FROM users WHERE username=? OR email=? LIMIT 1";
            $stmt = mysqli_prepare($connection, $query);
            mysqli_stmt_bind_param($stmt, "ss", $username_email, $username_email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) == 1) {
                $user = mysqli_fetch_assoc($result);
                if (password_verify($password, $user['password'])) {
                    if (array_key_exists('is_verified', $user) && (int) $user['is_verified'] !== 1) {
                        $_SESSION['signin'] = "Please confirm your account before signing in.";
                        $_SESSION['verification_email'] = $user['email'];
                        header('location: ' . ROOT_URL . 'verify.php?email=' . urlencode($user['email']));
                        die();
                    }
                    // Set session
                    $_SESSION['user-id'] = $user['id'];
                    if ($user['is_admin'] == 1) {
                        $_SESSION['user_is_admin'] = true;
                    }
                    
                    // Remember me functionality
                    if (isset($_POST['remember_me']) && $_POST['remember_me'] == 1) {
                        $token = bin2hex(random_bytes(32));
                        $update_query = "UPDATE users SET remember_token=? WHERE id=?";
                        $update_stmt = mysqli_prepare($connection, $update_query);
                        mysqli_stmt_bind_param($update_stmt, "si", $token, $user['id']);
                        mysqli_stmt_execute($update_stmt);
                        
                        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/');
                    }
                    
                    // Redirect based on user type
                    if (isset($_SESSION['user_is_admin']) && $_SESSION['user_is_admin']) {
                        header('location: ' . ROOT_URL . 'admin/');
                    } else {
header('location: ' . ROOT_URL . 'user-dashboard.php');
                    }
                    die();
                } else {
                    $_SESSION['signin'] = "Password is not correct";
                }
            } else {
                $_SESSION['signin'] = "User not found";
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    // Pass form data back
    $_SESSION['signin-data'] = $_POST;
    header('location: ' . ROOT_URL . 'signin.php');
    die();
} else {
    header('location: ' . ROOT_URL . 'signin.php');
    die();
}
?>

