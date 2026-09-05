<?php
session_start();
require 'config/constants.php';

//get back form data if there is a registration error
$firstname = $_SESSION['signup-data']['firstname'] ?? NULL;
$lastname = $_SESSION['signup-data']['lastname'] ?? NULL;
$username = $_SESSION['signup-data']['username'] ?? NULL;
$email = $_SESSION['signup-data']['email'] ?? NULL;
$phone = $_SESSION['signup-data']['phone'] ?? NULL;
$createpassword = $_SESSION['signup-data']['createpassword'] ?? NULL;
$confirmpassword = $_SESSION['signup-data']['confirmpassword'] ?? NULL;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE-edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Fantepedia System</title>

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
                    <h2>Join Fantepedia
                    <p>Become part of the Fante heritage preservation community. Share knowledge, contribute content, and explore our cultural treasures.</p>
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
                    <h2>Sign Up</h2>

                    <?php if(isset($_SESSION['signup'])): ?>
                        <div class="alert__message error">
                            <p><?= $_SESSION['signup']; unset($_SESSION['signup']); ?></p>
                        </div>
                    <?php endif; ?>

                    <form action="<?= ROOT_URL ?>signup-logic.php" class="auth-form" enctype="multipart/form-data" method="POST">
<div class="form-row">
                            <div class="form-group">
                                <label for="firstname">First Name</label>
                                <input type="text" id="firstname" name="firstname" value="<?= $firstname ?>" placeholder="First Name" required>
                            </div>
                            <div class="form-group">
                                <label for="lastname">Last Name</label>
                                <input type="text" id="lastname" name="lastname" value="<?= $lastname ?>" placeholder="Last Name" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" value="<?= $username ?>" placeholder="Username" required>
                        </div>

<div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?= $email ?>" placeholder="Email" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Mobile Number</label>
                            <div class="phone-input-container">
                                <select id="country-code" name="country_code" class="country-code-select" required>
                                    <option value="+93" <?= ($phone && strpos($phone, '+93') === 0) ? 'selected' : '' ?>>🇦🇫 Afghanistan (+93)</option>
                                    <option value="+355" <?= ($phone && strpos($phone, '+355') === 0) ? 'selected' : '' ?>>🇦🇱 Albania (+355)</option>
                                    <option value="+213" <?= ($phone && strpos($phone, '+213') === 0) ? 'selected' : '' ?>>🇩🇿 Algeria (+213)</option>
                                    <option value="+1684" <?= ($phone && strpos($phone, '+1684') === 0) ? 'selected' : '' ?>>🇦🇸 American Samoa (+1684)</option>
                                    <option value="+376" <?= ($phone && strpos($phone, '+376') === 0) ? 'selected' : '' ?>>🇦🇩 Andorra (+376)</option>
                                    <option value="+244" <?= ($phone && strpos($phone, '+244') === 0) ? 'selected' : '' ?>>🇦🇴 Angola (+244)</option>
                                    <option value="+1264" <?= ($phone && strpos($phone, '+1264') === 0) ? 'selected' : '' ?>>🇦🇮 Anguilla (+1264)</option>
                                    <option value="+1268" <?= ($phone && strpos($phone, '+1268') === 0) ? 'selected' : '' ?>>🇦🇬 Antigua & Barbuda (+1268)</option>
                                    <option value="+54" <?= ($phone && strpos($phone, '+54') === 0) ? 'selected' : '' ?>>🇦🇷 Argentina (+54)</option>
                                    <option value="+374" <?= ($phone && strpos($phone, '+374') === 0) ? 'selected' : '' ?>>🇦🇲 Armenia (+374)</option>
                                    <option value="+297" <?= ($phone && strpos($phone, '+297') === 0) ? 'selected' : '' ?>>🇦🇼 Aruba (+297)</option>
                                    <option value="+61" <?= ($phone && strpos($phone, '+61') === 0) ? 'selected' : '' ?>>🇦🇺 Australia (+61)</option>
                                    <option value="+43" <?= ($phone && strpos($phone, '+43') === 0) ? 'selected' : '' ?>>🇦🇹 Austria (+43)</option>
                                    <option value="+994" <?= ($phone && strpos($phone, '+994') === 0) ? 'selected' : '' ?>>🇦🇿 Azerbaijan (+994)</option>
                                    <option value="+1242" <?= ($phone && strpos($phone, '+1242') === 0) ? 'selected' : '' ?>>🇧🇸 Bahamas (+1242)</option>
                                    <option value="+973" <?= ($phone && strpos($phone, '+973') === 0) ? 'selected' : '' ?>>🇧🇭 Bahrain (+973)</option>
                                    <option value="+880" <?= ($phone && strpos($phone, '+880') === 0) ? 'selected' : '' ?>>🇧🇩 Bangladesh (+880)</option>
                                    <option value="+1246" <?= ($phone && strpos($phone, '+1246') === 0) ? 'selected' : '' ?>>🇧🇧 Barbados (+1246)</option>
                                    <option value="+375" <?= ($phone && strpos($phone, '+375') === 0) ? 'selected' : '' ?>>🇧🇾 Belarus (+375)</option>
                                    <option value="+32" <?= ($phone && strpos($phone, '+32') === 0) ? 'selected' : '' ?>>🇧🇪 Belgium (+32)</option>
                                    <option value="+501" <?= ($phone && strpos($phone, '+501') === 0) ? 'selected' : '' ?>>🇧🇿 Belize (+501)</option>
                                    <option value="+229" <?= ($phone && strpos($phone, '+229') === 0) ? 'selected' : '' ?>>🇧🇯 Benin (+229)</option>
                                    <option value="+1441" <?= ($phone && strpos($phone, '+1441') === 0) ? 'selected' : '' ?>>🇧🇲 Bermuda (+1441)</option>
                                    <option value="+975" <?= ($phone && strpos($phone, '+975') === 0) ? 'selected' : '' ?>>🇧🇹 Bhutan (+975)</option>
                                    <option value="+591" <?= ($phone && strpos($phone, '+591') === 0) ? 'selected' : '' ?>>🇧🇴 Bolivia (+591)</option>
                                    <option value="+387" <?= ($phone && strpos($phone, '+387') === 0) ? 'selected' : '' ?>>🇧🇦 Bosnia & Herzegovina (+387)</option>
                                    <option value="+267" <?= ($phone && strpos($phone, '+267') === 0) ? 'selected' : '' ?>>🇧🇼 Botswana (+267)</option>
                                    <option value="+55" <?= ($phone && strpos($phone, '+55') === 0) ? 'selected' : '' ?>>🇧🇷 Brazil (+55)</option>
                                    <option value="+246" <?= ($phone && strpos($phone, '+246') === 0) ? 'selected' : '' ?>>🇮🇴 British Indian Ocean Terr. (+246)</option>
                                    <option value="+1284" <?= ($phone && strpos($phone, '+1284') === 0) ? 'selected' : '' ?>>🇻🇬 British Virgin Islands (+1284)</option>
                                    <option value="+673" <?= ($phone && strpos($phone, '+673') === 0) ? 'selected' : '' ?>>🇧🇳 Brunei (+673)</option>
                                    <option value="+359" <?= ($phone && strpos($phone, '+359') === 0) ? 'selected' : '' ?>>🇧🇬 Bulgaria (+359)</option>
                                    <option value="+226" <?= ($phone && strpos($phone, '+226') === 0) ? 'selected' : '' ?>>🇧🇫 Burkina Faso (+226)</option>
                                    <option value="+257" <?= ($phone && strpos($phone, '+257') === 0) ? 'selected' : '' ?>>🇧🇮 Burundi (+257)</option>
                                    <option value="+855" <?= ($phone && strpos($phone, '+855') === 0) ? 'selected' : '' ?>>🇰🇭 Cambodia (+855)</option>
                                    <option value="+237" <?= ($phone && strpos($phone, '+237') === 0) ? 'selected' : '' ?>>🇨🇲 Cameroon (+237)</option>
                                    <option value="+1" <?= ($phone && strpos($phone, '+1') === 0) ? 'selected' : '' ?>>🇨🇦 Canada (+1)</option>
                                    <option value="+238" <?= ($phone && strpos($phone, '+238') === 0) ? 'selected' : '' ?>>🇨🇻 Cape Verde (+238)</option>
                                    <option value="+1345" <?= ($phone && strpos($phone, '+1345') === 0) ? 'selected' : '' ?>>🇰🇾 Cayman Islands (+1345)</option>
                                    <option value="+236" <?= ($phone && strpos($phone, '+236') === 0) ? 'selected' : '' ?>>🇨🇫 Central African Republic (+236)</option>
                                    <option value="+235" <?= ($phone && strpos($phone, '+235') === 0) ? 'selected' : '' ?>>🇹🇩 Chad (+235)</option>
                                    <option value="+56" <?= ($phone && strpos($phone, '+56') === 0) ? 'selected' : '' ?>>🇨🇱 Chile (+56)</option>
                                    <option value="+86" <?= ($phone && strpos($phone, '+86') === 0) ? 'selected' : '' ?>>🇨🇳 China (+86)</option>
                                    <option value="+57" <?= ($phone && strpos($phone, '+57') === 0) ? 'selected' : '' ?>>🇨🇴 Colombia (+57)</option>
                                    <option value="+269" <?= ($phone && strpos($phone, '+269') === 0) ? 'selected' : '' ?>>🇰🇲 Comoros (+269)</option>
                                    <option value="+682" <?= ($phone && strpos($phone, '+682') === 0) ? 'selected' : '' ?>>🇨🇰 Cook Islands (+682)</option>
                                    <option value="+506" <?= ($phone && strpos($phone, '+506') === 0) ? 'selected' : '' ?>>🇨🇷 Costa Rica (+506)</option>
                                    <option value="+225" <?= ($phone && strpos($phone, '+225') === 0) ? 'selected' : '' ?>>🇨🇮 Côte d'Ivoire (+225)</option>
                                    <option value="+385" <?= ($phone && strpos($phone, '+385') === 0) ? 'selected' : '' ?>>🇭🇷 Croatia (+385)</option>
                                    <option value="+53" <?= ($phone && strpos($phone, '+53') === 0) ? 'selected' : '' ?>>🇨🇺 Cuba (+53)</option>
                                    <option value="+357" <?= ($phone && strpos($phone, '+357') === 0) ? 'selected' : '' ?>>🇨🇾 Cyprus (+357)</option>
                                    <option value="+420" <?= ($phone && strpos($phone, '+420') === 0) ? 'selected' : '' ?>>🇨🇿 Czech Republic (+420)</option>
                                    <option value="+243" <?= ($phone && strpos($phone, '+243') === 0) ? 'selected' : '' ?>>🇨🇩 DR Congo (+243)</option>
                                    <option value="+45" <?= ($phone && strpos($phone, '+45') === 0) ? 'selected' : '' ?>>🇩🇰 Denmark (+45)</option>
                                    <option value="+253" <?= ($phone && strpos($phone, '+253') === 0) ? 'selected' : '' ?>>🇩🇯 Djibouti (+253)</option>
                                    <option value="+1767" <?= ($phone && strpos($phone, '+1767') === 0) ? 'selected' : '' ?>>🇩🇲 Dominica (+1767)</option>
                                    <option value="+1809" <?= ($phone && strpos($phone, '+1809') === 0) ? 'selected' : '' ?>>🇩🇴 Dominican Republic (+1809)</option>
                                    <option value="+1829" <?= ($phone && strpos($phone, '+1829') === 0) ? 'selected' : '' ?>>🇩🇴 Dominican Republic (+1829)</option>
                                    <option value="+1849" <?= ($phone && strpos($phone, '+1849') === 0) ? 'selected' : '' ?>>🇩🇴 Dominican Republic (+1849)</option>
                                    <option value="+670" <?= ($phone && strpos($phone, '+670') === 0) ? 'selected' : '' ?>>🇹🇱 East Timor (+670)</option>
                                    <option value="+593" <?= ($phone && strpos($phone, '+593') === 0) ? 'selected' : '' ?>>🇪🇨 Ecuador (+593)</option>
                                    <option value="+20" <?= ($phone && strpos($phone, '+20') === 0) ? 'selected' : '' ?>>🇪🇬 Egypt (+20)</option>
                                    <option value="+503" <?= ($phone && strpos($phone, '+503') === 0) ? 'selected' : '' ?>>🇸🇻 El Salvador (+503)</option>
                                    <option value="+240" <?= ($phone && strpos($phone, '+240') === 0) ? 'selected' : '' ?>>🇬🇶 Equatorial Guinea (+240)</option>
                                    <option value="+291" <?= ($phone && strpos($phone, '+291') === 0) ? 'selected' : '' ?>>🇪🇷 Eritrea (+291)</option>
                                    <option value="+372" <?= ($phone && strpos($phone, '+372') === 0) ? 'selected' : '' ?>>🇪🇪 Estonia (+372)</option>
                                    <option value="+251" <?= ($phone && strpos($phone, '+251') === 0) ? 'selected' : '' ?>>🇪🇹 Ethiopia (+251)</option>
                                    <option value="+500" <?= ($phone && strpos($phone, '+500') === 0) ? 'selected' : '' ?>>🇫🇰 Falkland Islands (+500)</option>
                                    <option value="+298" <?= ($phone && strpos($phone, '+298') === 0) ? 'selected' : '' ?>>🇫🇴 Faroe Islands (+298)</option>
                                    <option value="+679" <?= ($phone && strpos($phone, '+679') === 0) ? 'selected' : '' ?>>🇫🇯 Fiji (+679)</option>
                                    <option value="+358" <?= ($phone && strpos($phone, '+358') === 0) ? 'selected' : '' ?>>🇫🇮 Finland (+358)</option>
                                    <option value="+33" <?= ($phone && strpos($phone, '+33') === 0) ? 'selected' : '' ?>>🇫🇷 France (+33)</option>
                                    <option value="+594" <?= ($phone && strpos($phone, '+594') === 0) ? 'selected' : '' ?>>🇬🇫 French Guiana (+594)</option>
                                    <option value="+689" <?= ($phone && strpos($phone, '+689') === 0) ? 'selected' : '' ?>>🇵🇫 French Polynesia (+689)</option>
                                    <option value="+241" <?= ($phone && strpos($phone, '+241') === 0) ? 'selected' : '' ?>>🇬🇦 Gabon (+241)</option>
                                    <option value="+220" <?= ($phone && strpos($phone, '+220') === 0) ? 'selected' : '' ?>>🇬🇲 Gambia (+220)</option>
                                    <option value="+995" <?= ($phone && strpos($phone, '+995') === 0) ? 'selected' : '' ?>>🇬🇪 Georgia (+995)</option>
                                    <option value="+49" <?= ($phone && strpos($phone, '+49') === 0) ? 'selected' : '' ?>>🇩🇪 Germany (+49)</option>
                                    <option value="+233" <?= ($phone && strpos($phone, '+233') === 0) ? 'selected' : '' ?>>🇬🇭 Ghana (+233)</option>
                                    <option value="+350" <?= ($phone && strpos($phone, '+350') === 0) ? 'selected' : '' ?>>🇬🇮 Gibraltar (+350)</option>
                                    <option value="+30" <?= ($phone && strpos($phone, '+30') === 0) ? 'selected' : '' ?>>🇬🇷 Greece (+30)</option>
                                    <option value="+299" <?= ($phone && strpos($phone, '+299') === 0) ? 'selected' : '' ?>>🇬🇱 Greenland (+299)</option>
                                    <option value="+1473" <?= ($phone && strpos($phone, '+1473') === 0) ? 'selected' : '' ?>>🇬🇩 Grenada (+1473)</option>
                                    <option value="+590" <?= ($phone && strpos($phone, '+590') === 0) ? 'selected' : '' ?>>🇬🇵 Guadeloupe (+590)</option>
                                    <option value="+1671" <?= ($phone && strpos($phone, '+1671') === 0) ? 'selected' : '' ?>>🇬🇺 Guam (+1671)</option>
                                    <option value="+502" <?= ($phone && strpos($phone, '+502') === 0) ? 'selected' : '' ?>>🇬🇹 Guatemala (+502)</option>
                                    <option value="+224" <?= ($phone && strpos($phone, '+224') === 0) ? 'selected' : '' ?>>🇬🇳 Guinea (+224)</option>
                                    <option value="+245" <?= ($phone && strpos($phone, '+245') === 0) ? 'selected' : '' ?>>🇬🇼 Guinea-Bissau (+245)</option>
                                    <option value="+592" <?= ($phone && strpos($phone, '+592') === 0) ? 'selected' : '' ?>>🇬🇾 Guyana (+592)</option>
                                    <option value="+509" <?= ($phone && strpos($phone, '+509') === 0) ? 'selected' : '' ?>>🇭🇹 Haiti (+509)</option>
                                    <option value="+504" <?= ($phone && strpos($phone, '+504') === 0) ? 'selected' : '' ?>>🇭🇳 Honduras (+504)</option>
                                    <option value="+852" <?= ($phone && strpos($phone, '+852') === 0) ? 'selected' : '' ?>>🇭🇰 Hong Kong (+852)</option>
                                    <option value="+36" <?= ($phone && strpos($phone, '+36') === 0) ? 'selected' : '' ?>>🇭🇺 Hungary (+36)</option>
                                    <option value="+354" <?= ($phone && strpos($phone, '+354') === 0) ? 'selected' : '' ?>>🇮🇸 Iceland (+354)</option>
                                    <option value="+91" <?= ($phone && strpos($phone, '+91') === 0) ? 'selected' : '' ?>>🇮🇳 India (+91)</option>
                                    <option value="+62" <?= ($phone && strpos($phone, '+62') === 0) ? 'selected' : '' ?>>🇮🇩 Indonesia (+62)</option>
                                    <option value="+98" <?= ($phone && strpos($phone, '+98') === 0) ? 'selected' : '' ?>>🇮🇷 Iran (+98)</option>
                                    <option value="+964" <?= ($phone && strpos($phone, '+964') === 0) ? 'selected' : '' ?>>🇮🇶 Iraq (+964)</option>
                                    <option value="+353" <?= ($phone && strpos($phone, '+353') === 0) ? 'selected' : '' ?>>🇮🇪 Ireland (+353)</option>
                                    <option value="+972" <?= ($phone && strpos($phone, '+972') === 0) ? 'selected' : '' ?>>🇮🇱 Israel (+972)</option>
                                    <option value="+39" <?= ($phone && strpos($phone, '+39') === 0) ? 'selected' : '' ?>>🇮🇹 Italy (+39)</option>
                                    <option value="+1876" <?= ($phone && strpos($phone, '+1876') === 0) ? 'selected' : '' ?>>🇯🇲 Jamaica (+1876)</option>
                                    <option value="+81" <?= ($phone && strpos($phone, '+81') === 0) ? 'selected' : '' ?>>🇯🇵 Japan (+81)</option>
                                    <option value="+962" <?= ($phone && strpos($phone, '+962') === 0) ? 'selected' : '' ?>>🇯🇴 Jordan (+962)</option>
                                    <option value="+77" <?= ($phone && strpos($phone, '+77') === 0) ? 'selected' : '' ?>>🇰🇿 Kazakhstan (+77)</option>
                                    <option value="+254" <?= ($phone && strpos($phone, '+254') === 0) ? 'selected' : '' ?>>🇰🇪 Kenya (+254)</option>
                                    <option value="+686" <?= ($phone && strpos($phone, '+686') === 0) ? 'selected' : '' ?>>🇰🇮 Kiribati (+686)</option>
                                    <option value="+383" <?= ($phone && strpos($phone, '+383') === 0) ? 'selected' : '' ?>>🇽🇰 Kosovo (+383)</option>
                                    <option value="+965" <?= ($phone && strpos($phone, '+965') === 0) ? 'selected' : '' ?>>🇰🇼 Kuwait (+965)</option>
                                    <option value="+996" <?= ($phone && strpos($phone, '+996') === 0) ? 'selected' : '' ?>>🇰🇬 Kyrgyzstan (+996)</option>
                                    <option value="+856" <?= ($phone && strpos($phone, '+856') === 0) ? 'selected' : '' ?>>🇱🇦 Laos (+856)</option>
                                    <option value="+371" <?= ($phone && strpos($phone, '+371') === 0) ? 'selected' : '' ?>>🇱🇻 Latvia (+371)</option>
                                    <option value="+961" <?= ($phone && strpos($phone, '+961') === 0) ? 'selected' : '' ?>>🇱🇧 Lebanon (+961)</option>
                                    <option value="+266" <?= ($phone && strpos($phone, '+266') === 0) ? 'selected' : '' ?>>🇱🇸 Lesotho (+266)</option>
                                    <option value="+231" <?= ($phone && strpos($phone, '+231') === 0) ? 'selected' : '' ?>>🇱🇷 Liberia (+231)</option>
                                    <option value="+218" <?= ($phone && strpos($phone, '+218') === 0) ? 'selected' : '' ?>>🇱🇾 Libya (+218)</option>
                                    <option value="+423" <?= ($phone && strpos($phone, '+423') === 0) ? 'selected' : '' ?>>🇱🇮 Liechtenstein (+423)</option>
                                    <option value="+370" <?= ($phone && strpos($phone, '+370') === 0) ? 'selected' : '' ?>>🇱🇹 Lithuania (+370)</option>
                                    <option value="+352" <?= ($phone && strpos($phone, '+352') === 0) ? 'selected' : '' ?>>🇱🇺 Luxembourg (+352)</option>
                                    <option value="+853" <?= ($phone && strpos($phone, '+853') === 0) ? 'selected' : '' ?>>🇲🇴 Macau (+853)</option>
                                    <option value="+389" <?= ($phone && strpos($phone, '+389') === 0) ? 'selected' : '' ?>>🇲🇰 Macedonia (+389)</option>
                                    <option value="+261" <?= ($phone && strpos($phone, '+261') === 0) ? 'selected' : '' ?>>🇲🇬 Madagascar (+261)</option>
                                    <option value="+265" <?= ($phone && strpos($phone, '+265') === 0) ? 'selected' : '' ?>>🇲🇼 Malawi (+265)</option>
                                    <option value="+60" <?= ($phone && strpos($phone, '+60') === 0) ? 'selected' : '' ?>>🇲🇾 Malaysia (+60)</option>
                                    <option value="+960" <?= ($phone && strpos($phone, '+960') === 0) ? 'selected' : '' ?>>🇲🇻 Maldives (+960)</option>
                                    <option value="+223" <?= ($phone && strpos($phone, '+223') === 0) ? 'selected' : '' ?>>🇲🇱 Mali (+223)</option>
                                    <option value="+356" <?= ($phone && strpos($phone, '+356') === 0) ? 'selected' : '' ?>>🇲🇹 Malta (+356)</option>
                                    <option value="+692" <?= ($phone && strpos($phone, '+692') === 0) ? 'selected' : '' ?>>🇲🇭 Marshall Islands (+692)</option>
                                    <option value="+596" <?= ($phone && strpos($phone, '+596') === 0) ? 'selected' : '' ?>>🇲🇶 Martinique (+596)</option>
                                    <option value="+222" <?= ($phone && strpos($phone, '+222') === 0) ? 'selected' : '' ?>>🇲🇷 Mauritania (+222)</option>
                                    <option value="+230" <?= ($phone && strpos($phone, '+230') === 0) ? 'selected' : '' ?>>🇲🇺 Mauritius (+230)</option>
                                    <option value="+262" <?= ($phone && strpos($phone, '+262') === 0) ? 'selected' : '' ?>>🇾🇹 Mayotte (+262)</option>
                                    <option value="+52" <?= ($phone && strpos($phone, '+52') === 0) ? 'selected' : '' ?>>🇲🇽 Mexico (+52)</option>
                                    <option value="+691" <?= ($phone && strpos($phone, '+691') === 0) ? 'selected' : '' ?>>🇫🇲 Micronesia (+691)</option>
                                    <option value="+373" <?= ($phone && strpos($phone, '+373') === 0) ? 'selected' : '' ?>>🇲🇩 Moldova (+373)</option>
                                    <option value="+377" <?= ($phone && strpos($phone, '+377') === 0) ? 'selected' : '' ?>>🇲🇨 Monaco (+377)</option>
                                    <option value="+976" <?= ($phone && strpos($phone, '+976') === 0) ? 'selected' : '' ?>>🇲🇳 Mongolia (+976)</option>
                                    <option value="+382" <?= ($phone && strpos($phone, '+382') === 0) ? 'selected' : '' ?>>🇲🇪 Montenegro (+382)</option>
                                    <option value="+1664" <?= ($phone && strpos($phone, '+1664') === 0) ? 'selected' : '' ?>>🇲🇸 Montserrat (+1664)</option>
                                    <option value="+212" <?= ($phone && strpos($phone, '+212') === 0) ? 'selected' : '' ?>>🇲🇦 Morocco (+212)</option>
                                    <option value="+258" <?= ($phone && strpos($phone, '+258') === 0) ? 'selected' : '' ?>>🇲🇿 Mozambique (+258)</option>
                                    <option value="+95" <?= ($phone && strpos($phone, '+95') === 0) ? 'selected' : '' ?>>🇲🇲 Myanmar (+95)</option>
                                    <option value="+264" <?= ($phone && strpos($phone, '+264') === 0) ? 'selected' : '' ?>>🇳🇦 Namibia (+264)</option>
                                    <option value="+674" <?= ($phone && strpos($phone, '+674') === 0) ? 'selected' : '' ?>>🇳🇷 Nauru (+674)</option>
                                    <option value="+977" <?= ($phone && strpos($phone, '+977') === 0) ? 'selected' : '' ?>>🇳🇵 Nepal (+977)</option>
                                    <option value="+31" <?= ($phone && strpos($phone, '+31') === 0) ? 'selected' : '' ?>>🇳🇱 Netherlands (+31)</option>
                                    <option value="+599" <?= ($phone && strpos($phone, '+599') === 0) ? 'selected' : '' ?>>🇳🇱 Netherlands Antilles (+599)</option>
                                    <option value="+687" <?= ($phone && strpos($phone, '+687') === 0) ? 'selected' : '' ?>>🇳🇨 New Caledonia (+687)</option>
                                    <option value="+64" <?= ($phone && strpos($phone, '+64') === 0) ? 'selected' : '' ?>>🇳🇿 New Zealand (+64)</option>
                                    <option value="+505" <?= ($phone && strpos($phone, '+505') === 0) ? 'selected' : '' ?>>🇳🇮 Nicaragua (+505)</option>
                                    <option value="+227" <?= ($phone && strpos($phone, '+227') === 0) ? 'selected' : '' ?>>🇳🇪 Niger (+227)</option>
                                    <option value="+234" <?= ($phone && strpos($phone, '+234') === 0) ? 'selected' : '' ?>>🇳🇬 Nigeria (+234)</option>
                                    <option value="+683" <?= ($phone && strpos($phone, '+683') === 0) ? 'selected' : '' ?>>🇳🇺 Niue (+683)</option>
                                    <option value="+850" <?= ($phone && strpos($phone, '+850') === 0) ? 'selected' : '' ?>>🇰🇵 North Korea (+850)</option>
                                    <option value="+1670" <?= ($phone && strpos($phone, '+1670') === 0) ? 'selected' : '' ?>>🇲🇵 Northern Mariana Islands (+1670)</option>
                                    <option value="+47" <?= ($phone && strpos($phone, '+47') === 0) ? 'selected' : '' ?>>🇳🇴 Norway (+47)</option>
                                    <option value="+968" <?= ($phone && strpos($phone, '+968') === 0) ? 'selected' : '' ?>>🇴🇲 Oman (+968)</option>
                                    <option value="+92" <?= ($phone && strpos($phone, '+92') === 0) ? 'selected' : '' ?>>🇵🇰 Pakistan (+92)</option>
                                    <option value="+680" <?= ($phone && strpos($phone, '+680') === 0) ? 'selected' : '' ?>>🇵🇼 Palau (+680)</option>
                                    <option value="+970" <?= ($phone && strpos($phone, '+970') === 0) ? 'selected' : '' ?>>🇵🇸 Palestine (+970)</option>
                                    <option value="+507" <?= ($phone && strpos($phone, '+507') === 0) ? 'selected' : '' ?>>🇵🇦 Panama (+507)</option>
                                    <option value="+675" <?= ($phone && strpos($phone, '+675') === 0) ? 'selected' : '' ?>>🇵🇬 Papua New Guinea (+675)</option>
                                    <option value="+595" <?= ($phone && strpos($phone, '+595') === 0) ? 'selected' : '' ?>>🇵🇾 Paraguay (+595)</option>
                                    <option value="+51" <?= ($phone && strpos($phone, '+51') === 0) ? 'selected' : '' ?>>🇵🇪 Peru (+51)</option>
                                    <option value="+63" <?= ($phone && strpos($phone, '+63') === 0) ? 'selected' : '' ?>>🇵🇭 Philippines (+63)</option>
                                    <option value="+48" <?= ($phone && strpos($phone, '+48') === 0) ? 'selected' : '' ?>>🇵🇱 Poland (+48)</option>
                                    <option value="+351" <?= ($phone && strpos($phone, '+351') === 0) ? 'selected' : '' ?>>🇵🇹 Portugal (+351)</option>
                                    <option value="+1787" <?= ($phone && strpos($phone, '+1787') === 0) ? 'selected' : '' ?>>🇵🇷 Puerto Rico (+1787)</option>
                                    <option value="+1939" <?= ($phone && strpos($phone, '+1939') === 0) ? 'selected' : '' ?>>🇵🇷 Puerto Rico (+1939)</option>
                                    <option value="+974" <?= ($phone && strpos($phone, '+974') === 0) ? 'selected' : '' ?>>🇶🇦 Qatar (+974)</option>
                                    <option value="+242" <?= ($phone && strpos($phone, '+242') === 0) ? 'selected' : '' ?>>🇨🇬 Republic of the Congo (+242)</option>
                                    <option value="+262" <?= ($phone && strpos($phone, '+262') === 0) ? 'selected' : '' ?>>🇷🇪 Réunion (+262)</option>
                                    <option value="+40" <?= ($phone && strpos($phone, '+40') === 0) ? 'selected' : '' ?>>🇷🇴 Romania (+40)</option>
                                    <option value="+7" <?= ($phone && strpos($phone, '+7') === 0) ? 'selected' : '' ?>>🇷🇺 Russia (+7)</option>
                                    <option value="+250" <?= ($phone && strpos($phone, '+250') === 0) ? 'selected' : '' ?>>🇷🇼 Rwanda (+250)</option>
                                    <option value="+290" <?= ($phone && strpos($phone, '+290') === 0) ? 'selected' : '' ?>>🇸🇭 Saint Helena (+290)</option>
                                    <option value="+1869" <?= ($phone && strpos($phone, '+1869') === 0) ? 'selected' : '' ?>>🇰🇳 Saint Kitts & Nevis (+1869)</option>
                                    <option value="+1758" <?= ($phone && strpos($phone, '+1758') === 0) ? 'selected' : '' ?>>🇱🇨 Saint Lucia (+1758)</option>
                                    <option value="+508" <?= ($phone && strpos($phone, '+508') === 0) ? 'selected' : '' ?>>🇵🇲 Saint Pierre & Miquelon (+508)</option>
                                    <option value="+1784" <?= ($phone && strpos($phone, '+1784') === 0) ? 'selected' : '' ?>>🇻🇨 Saint Vincent & Grenadines (+1784)</option>
                                    <option value="+685" <?= ($phone && strpos($phone, '+685') === 0) ? 'selected' : '' ?>>🇼🇸 Samoa (+685)</option>
                                    <option value="+378" <?= ($phone && strpos($phone, '+378') === 0) ? 'selected' : '' ?>>🇸🇲 San Marino (+378)</option>
                                    <option value="+239" <?= ($phone && strpos($phone, '+239') === 0) ? 'selected' : '' ?>>🇸🇹 São Tomé & Príncipe (+239)</option>
                                    <option value="+966" <?= ($phone && strpos($phone, '+966') === 0) ? 'selected' : '' ?>>🇸🇦 Saudi Arabia (+966)</option>
                                    <option value="+221" <?= ($phone && strpos($phone, '+221') === 0) ? 'selected' : '' ?>>🇸🇳 Senegal (+221)</option>
                                    <option value="+381" <?= ($phone && strpos($phone, '+381') === 0) ? 'selected' : '' ?>>🇷🇸 Serbia (+381)</option>
                                    <option value="+248" <?= ($phone && strpos($phone, '+248') === 0) ? 'selected' : '' ?>>🇸🇨 Seychelles (+248)</option>
                                    <option value="+232" <?= ($phone && strpos($phone, '+232') === 0) ? 'selected' : '' ?>>🇸🇱 Sierra Leone (+232)</option>
                                    <option value="+65" <?= ($phone && strpos($phone, '+65') === 0) ? 'selected' : '' ?>>🇸🇬 Singapore (+65)</option>
                                    <option value="+421" <?= ($phone && strpos($phone, '+421') === 0) ? 'selected' : '' ?>>🇸🇰 Slovakia (+421)</option>
                                    <option value="+386" <?= ($phone && strpos($phone, '+386') === 0) ? 'selected' : '' ?>>🇸🇮 Slovenia (+386)</option>
                                    <option value="+677" <?= ($phone && strpos($phone, '+677') === 0) ? 'selected' : '' ?>>🇸🇧 Solomon Islands (+677)</option>
                                    <option value="+252" <?= ($phone && strpos($phone, '+252') === 0) ? 'selected' : '' ?>>🇸🇴 Somalia (+252)</option>
                                    <option value="+27" <?= ($phone && strpos($phone, '+27') === 0) ? 'selected' : '' ?>>🇿🇦 South Africa (+27)</option>
                                    <option value="+82" <?= ($phone && strpos($phone, '+82') === 0) ? 'selected' : '' ?>>🇰🇷 South Korea (+82)</option>
                                    <option value="+211" <?= ($phone && strpos($phone, '+211') === 0) ? 'selected' : '' ?>>🇸🇸 South Sudan (+211)</option>
                                    <option value="+34" <?= ($phone && strpos($phone, '+34') === 0) ? 'selected' : '' ?>>🇪🇸 Spain (+34)</option>
                                    <option value="+94" <?= ($phone && strpos($phone, '+94') === 0) ? 'selected' : '' ?>>🇱🇰 Sri Lanka (+94)</option>
                                    <option value="+249" <?= ($phone && strpos($phone, '+249') === 0) ? 'selected' : '' ?>>🇸🇩 Sudan (+249)</option>
                                    <option value="+597" <?= ($phone && strpos($phone, '+597') === 0) ? 'selected' : '' ?>>🇸🇷 Suriname (+597)</option>
                                    <option value="+268" <?= ($phone && strpos($phone, '+268') === 0) ? 'selected' : '' ?>>🇸🇿 Swaziland (+268)</option>
                                    <option value="+46" <?= ($phone && strpos($phone, '+46') === 0) ? 'selected' : '' ?>>🇸🇪 Sweden (+46)</option>
                                    <option value="+41" <?= ($phone && strpos($phone, '+41') === 0) ? 'selected' : '' ?>>🇨🇭 Switzerland (+41)</option>
                                    <option value="+963" <?= ($phone && strpos($phone, '+963') === 0) ? 'selected' : '' ?>>🇸🇾 Syria (+963)</option>
                                    <option value="+886" <?= ($phone && strpos($phone, '+886') === 0) ? 'selected' : '' ?>>🇹🇼 Taiwan (+886)</option>
                                    <option value="+992" <?= ($phone && strpos($phone, '+992') === 0) ? 'selected' : '' ?>>🇹🇯 Tajikistan (+992)</option>
                                    <option value="+255" <?= ($phone && strpos($phone, '+255') === 0) ? 'selected' : '' ?>>🇹🇿 Tanzania (+255)</option>
                                    <option value="+66" <?= ($phone && strpos($phone, '+66') === 0) ? 'selected' : '' ?>>🇹🇭 Thailand (+66)</option>
                                    <option value="+228" <?= ($phone && strpos($phone, '+228') === 0) ? 'selected' : '' ?>>🇹🇬 Togo (+228)</option>
                                    <option value="+690" <?= ($phone && strpos($phone, '+690') === 0) ? 'selected' : '' ?>>🇹🇰 Tokelau (+690)</option>
                                    <option value="+676" <?= ($phone && strpos($phone, '+676') === 0) ? 'selected' : '' ?>>🇹🇴 Tonga (+676)</option>
                                    <option value="+1868" <?= ($phone && strpos($phone, '+1868') === 0) ? 'selected' : '' ?>>🇹🇹 Trinidad & Tobago (+1868)</option>
                                    <option value="+216" <?= ($phone && strpos($phone, '+216') === 0) ? 'selected' : '' ?>>🇹🇳 Tunisia (+216)</option>
                                    <option value="+90" <?= ($phone && strpos($phone, '+90') === 0) ? 'selected' : '' ?>>🇹🇷 Turkey (+90)</option>
                                    <option value="+993" <?= ($phone && strpos($phone, '+993') === 0) ? 'selected' : '' ?>>🇹🇲 Turkmenistan (+993)</option>
                                    <option value="+1649" <?= ($phone && strpos($phone, '+1649') === 0) ? 'selected' : '' ?>>🇹🇨 Turks & Caicos Islands (+1649)</option>
                                    <option value="+688" <?= ($phone && strpos($phone, '+688') === 0) ? 'selected' : '' ?>>🇹🇻 Tuvalu (+688)</option>
                                    <option value="+256" <?= ($phone && strpos($phone, '+256') === 0) ? 'selected' : '' ?>>🇺🇬 Uganda (+256)</option>
                                    <option value="+380" <?= ($phone && strpos($phone, '+380') === 0) ? 'selected' : '' ?>>🇺🇦 Ukraine (+380)</option>
                                    <option value="+971" <?= ($phone && strpos($phone, '+971') === 0) ? 'selected' : '' ?>>🇦🇪 United Arab Emirates (+971)</option>
                                    <option value="+44" <?= ($phone && strpos($phone, '+44') === 0) ? 'selected' : '' ?>>🇬🇧 United Kingdom (+44)</option>
                                    <option value="+1" <?= ($phone && strpos($phone, '+1') === 0) ? 'selected' : '' ?>>🇺🇸 United States (+1)</option>
                                    <option value="+598" <?= ($phone && strpos($phone, '+598') === 0) ? 'selected' : '' ?>>🇺🇾 Uruguay (+598)</option>
                                    <option value="+998" <?= ($phone && strpos($phone, '+998') === 0) ? 'selected' : '' ?>>🇺🇿 Uzbekistan (+998)</option>
                                    <option value="+678" <?= ($phone && strpos($phone, '+678') === 0) ? 'selected' : '' ?>>🇻🇺 Vanuatu (+678)</option>
                                    <option value="+379" <?= ($phone && strpos($phone, '+379') === 0) ? 'selected' : '' ?>>🇻🇦 Vatican City (+379)</option>
                                    <option value="+58" <?= ($phone && strpos($phone, '+58') === 0) ? 'selected' : '' ?>>🇻🇪 Venezuela (+58)</option>
                                    <option value="+84" <?= ($phone && strpos($phone, '+84') === 0) ? 'selected' : '' ?>>🇻🇳 Vietnam (+84)</option>
                                    <option value="+1284" <?= ($phone && strpos($phone, '+1284') === 0) ? 'selected' : '' ?>>🇻🇬 Virgin Islands (British) (+1284)</option>
                                    <option value="+1340" <?= ($phone && strpos($phone, '+1340') === 0) ? 'selected' : '' ?>>🇻🇮 Virgin Islands (US) (+1340)</option>
                                    <option value="+681" <?= ($phone && strpos($phone, '+681') === 0) ? 'selected' : '' ?>>🇼🇫 Wallis & Futuna (+681)</option>
                                    <option value="+967" <?= ($phone && strpos($phone, '+967') === 0) ? 'selected' : '' ?>>🇾🇪 Yemen (+967)</option>
                                    <option value="+260" <?= ($phone && strpos($phone, '+260') === 0) ? 'selected' : '' ?>>🇿🇲 Zambia (+260)</option>
                                    <option value="+263" <?= ($phone && strpos($phone, '+263') === 0) ? 'selected' : '' ?>>🇿🇼 Zimbabwe (+263)</option>
                                </select>
                                <input type="tel" id="phone" name="phone" value="<?= $phone ? htmlspecialchars(substr($phone, strpos($phone, ' ') !== false ? strpos($phone, ' ') + 1 : 4)) : '' ?>" placeholder="501234567" required>
                            </div>
                            <small class="phone-hint">Enter your mobile number with country code for SMS confirmation.</small>
                        </div>

                        <div class="form-row">
                            <div class="form-group password-container">
                                <label for="createpassword">Create Password</label>
                                <input type="password" id="createpassword" name="createpassword" value="<?= $createpassword ?>" placeholder="Create Password" required>
                                <i class="fas fa-eye"></i>
                            </div>
                            <div class="form-group password-container">
                                <label for="confirmpassword">Confirm Password</label>
                                <input type="password" id="confirmpassword" name="confirmpassword" value="<?= $confirmpassword ?>" placeholder="Confirm Password" required>
                                <i class="fas fa-eye"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="avatar">Profile Picture</label>
                            <input type="file" name="avatar" id="avatar" accept="image/*" required>
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

                        <button type="submit" name="submit" class="btn">Create Account</button>
                    </form>

                    <div class="divider">
                        <span>or sign up with</span>
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
                        <small>Already have an account? <a href="<?= ROOT_URL ?>signin.php">Sign In</a></small>
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
                <li>No spam/harassment/illegal content</li>
                <li>Respect Fante cultural heritage</li>
                <li>License to use contributions educationally</li>
            </ul>
            <p class="modal-note">Full terms apply. <a href="<?= ROOT_URL ?>terms-and-conditions.php" target="_blank">Read complete version</a>.</p>
        </div>
    </div>

    <?php include 'partials/footer.php'; ?>
</body>
</html>

