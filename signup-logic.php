<?php
session_start();
require 'config/database.php';
require 'config/constants.php';
require 'includes/sms-service.php';
require 'includes/email-service.php';

// get signup form data if signup button is clicked
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
        $_SESSION['signup'] = "reCAPTCHA verification failed. Please try again.";
    } else {
        $firstname = isset($_POST['firstname']) ? filter_var($_POST['firstname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
        $lastname = isset($_POST['lastname']) ? filter_var($_POST['lastname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
        $username = isset($_POST['username']) ? filter_var($_POST['username'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
$email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) : false;
        $country_code = isset($_POST['country_code']) ? filter_var($_POST['country_code'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
        $phone_number = isset($_POST['phone']) ? filter_var($_POST['phone'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
        $full_phone = $country_code . preg_replace('/\D+/', '', $phone_number);
        $createpassword = isset($_POST['createpassword']) ? filter_var($_POST['createpassword'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
        $confirmpassword = isset($_POST['confirmpassword']) ? filter_var($_POST['confirmpassword'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
        $avatar = $_FILES['avatar'] ?? [];
        // Terms agreement captured

        //validate input values
        if (!$firstname) {
            $_SESSION['signup'] = "Please enter your First name";
        } elseif (!$lastname) {
            $_SESSION['signup'] = "Please enter your Last name";
        } elseif (!$username) {
             $_SESSION['signup'] = "Please enter your Last name";
        } elseif (!$username) {
             $_SESSION['signup'] = "Please enter your Username";
} elseif (!$email) {
             $_SESSION['signup'] = "Please enter a valid email address";
        } elseif (empty($country_code) || empty($phone_number)) {
            $_SESSION['signup'] = "Please enter your mobile number with country code";
        } elseif (!preg_match('/^[0-9]{5,15}$/', $phone_number)) {
            $_SESSION['signup'] = "Please enter a valid phone number (5-15 digits)";
        } elseif (strlen($createpassword) < 8 || strlen ($confirmpassword) < 8) {
             $_SESSION['signup'] = "Password should be 8+ characters";
        } elseif (empty($_POST['terms'])) {
            $_SESSION['signup'] = "You must agree to Terms & Conditions";
        } elseif (!$avatar['name']) {
             $_SESSION['signup'] = "Please add avatar";
        } else {
            // check if passwords match
            if($createpassword !== $confirmpassword) {
                $_SESSION['signup'] = "Passwords do not match";
            } else {
                //hash password
                $hashed_password = password_hash($createpassword, PASSWORD_DEFAULT);

                // check if username or email already exist in database
                $stmt = mysqli_prepare($connection, "SELECT * FROM users WHERE username=? OR email=?");
                if (!$stmt) {
                    $_SESSION['signup'] = "Database prepare error: " . mysqli_error($connection);
                } else {
                    mysqli_stmt_bind_param($stmt, "ss", $username, $email);
                    if (!mysqli_stmt_execute($stmt)) {
                        $_SESSION['signup'] = "Database execute error: " . mysqli_stmt_error($stmt);
                    } else {
                        $user_check_result = mysqli_stmt_get_result($stmt);
                        if (mysqli_num_rows($user_check_result) > 0) {
                            $_SESSION['signup'] = "Username or Email already exist";
                        } else {
                            // proceed to insert
                        }
                    }
                    mysqli_stmt_close($stmt);
                }
                if (isset($_SESSION['signup'])) {
                    // redirect back if error
                } else {
                    // work on avatar
                    //rename avatar
                    $time = time(); // make each image name unique using current timestamp
                    $avatar_name = $time . $avatar['name'];
                    $avatar_tmp_name = $avatar['tmp_name'];
                    $avatar_destination_path = 'images/' . $avatar_name;

                    // make sure file is an image
                    $allowed_files = ['png', 'jpg', 'jpeg'];
                    $extension = explode('.', $avatar_name);
                    $extension = end($extension);
                    if(in_array($extension, $allowed_files)) {
                    // make sure image is not too large (2mb+)
                    if($avatar['size'] < 2000000) {
                        //upload avatar
                        if(move_uploaded_file($avatar_tmp_name, $avatar_destination_path)) {
                            $verification_token = bin2hex(random_bytes(32));
                            $verification_code = (string) random_int(100000, 999999);
                            $verification_expires_at = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                            $verification_token_hash = hash('sha256', $verification_token);
                            $verification_code_hash = password_hash($verification_code, PASSWORD_DEFAULT);
                            $stmt = mysqli_prepare($connection, "INSERT INTO users (firstname, lastname, username, email, phone, password, avatar, is_admin, is_verified, verification_token_hash, verification_code_hash, verification_expires_at) VALUES(?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?)");
                            if (!$stmt) {
                                $_SESSION['signup'] = "Database prepare error: " . mysqli_error($connection);
                            } else {
                                $is_admin = 0;
                                mysqli_stmt_bind_param($stmt, "sssssssisss", $firstname, $lastname, $username, $email, $full_phone, $hashed_password, $avatar_name, $is_admin, $verification_token_hash, $verification_code_hash, $verification_expires_at);
                                if (!mysqli_stmt_execute($stmt)) {
                                    $_SESSION['signup'] = "Database execute error: " . mysqli_stmt_error($stmt);
                                } else {
                                    if (mysqli_stmt_affected_rows($stmt) > 0) {
                                        $verification_link = ROOT_URL . 'verify-logic.php?token=' . urlencode($verification_token);
                                        $email_sent = sendAccountVerificationEmail($email, $firstname, $verification_link, $verification_code);
                                        $sms_result = sendSMS($full_phone, "Fantepedia verification code: {$verification_code}. It expires in 30 minutes.");
                                        $_SESSION['verification_email'] = $email;
                                        $_SESSION['verification-message'] = 'A verification code and confirmation link have been sent to your registered email or mobile number.';
                                        if (!$email_sent && !$sms_result['success']) {
                                            $_SESSION['verification-error'] = 'We could not send the verification notification. Contact support before trying again.';
                                        }
                                        header('location: ' . ROOT_URL . 'verify.php?email=' . urlencode($email));
                                        die();
                                    } else {
                                        $_SESSION['signup'] = "Registration failed";
                                    }
                                }
                                mysqli_stmt_close($stmt);
                            }
                        } else {
                            $_SESSION['signup'] = "Failed to upload avatar";
                        }
                    }   else {
                        $_SESSION['signup'] = "File size too big. Should be less than 2mb";
                    }
                    } else {
                        $_SESSION['signup'] = "File should be png, jpg or jpeg";
                    }
                }
            }
        }
    }

    // redirect back to signup page if there is any problem
    if(isset($_SESSION['signup'])) {
        //pass form data back to signup page
        $_SESSION['signup-data'] = $_POST;
        header('location: ' . ROOT_URL . 'signup.php');
        die();
    }

} else {
    //if button isn't clicked, bounced back to signup page
    header('location: ' . ROOT_URL . 'signup.php');
    die();
}
