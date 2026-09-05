<?php
session_start();
include 'partials/header.php';

// Database connection
require 'config/database.php';

// Get dynamic stats
$articles_count_query = "SELECT COUNT(*) as count FROM posts";
$articles_count_result = mysqli_query($connection, $articles_count_query);
$articles_count = mysqli_fetch_assoc($articles_count_result)['count'];

$users_count_query = "SELECT COUNT(*) as count FROM users";
$users_count_result = mysqli_query($connection, $users_count_query);
$users_count = mysqli_fetch_assoc($users_count_result)['count'];

$contributors_count_query = "SELECT COUNT(DISTINCT user_id) as count FROM posts";
$contributors_count_result = mysqli_query($connection, $contributors_count_query);
$contributors_count = mysqli_fetch_assoc($contributors_count_result)['count'];

// Fetch services category
$services_category_query = "SELECT * FROM categories WHERE title LIKE '%service%' LIMIT 1";
$services_category_result = mysqli_query($connection, $services_category_query);
$services_category = mysqli_fetch_assoc($services_category_result);

// If services category exists, fetch posts from it
if ($services_category) {
    $services_posts_query = "SELECT p.*, u.firstname, u.lastname, u.avatar
                            FROM posts p
                            LEFT JOIN users u ON p.user_id = u.id
                            WHERE p.category_id = {$services_category['id']}
                            ORDER BY p.date_time DESC";
    $services_posts = mysqli_query($connection, $services_posts_query);
}

// Fetch featured service post
$featured_service_query = "SELECT p.*, u.firstname, u.lastname, u.avatar
                          FROM posts p
                          LEFT JOIN users u ON p.user_id = u.id
                          WHERE p.category_id = " . ($services_category ? $services_category['id'] : 0) . "
                          AND p.is_featured = 1
                          ORDER BY p.date_time DESC LIMIT 1";
$featured_service_result = mysqli_query($connection, $featured_service_query);
$featured_service = mysqli_fetch_assoc($featured_service_result);

?>
    <!..==================================END OF NAV============================================>

    <script>
        // Set user logged in status for services.js
        document.body.setAttribute('data-user-logged-in', '<?php echo isset($_SESSION['user-id']) ? 'true' : 'false'; ?>');
    </script>
    <script src="js/services.js"></script>

    <!-- Hero Section -->
<section class="dashboard services-hero modern-hero section__extra-margin">
        <div class="container services-hero__container">

            <div class="services-hero__image">
                <img src="./images/<?= $featured_service ? $featured_service['thumbnail'] : 'default-avatar.svg' ?>" alt="Fante Culture Services" onerror="this.src='./images/default-avatar.svg'">
            </div>
            <div class="services-hero__content">
                <h1>Our Services</h1>
                <p>Discover comprehensive services designed to preserve and promote Fante cultural heritage through education, research, and community engagement.</p>
                <div class="services-stats">
                    <div class="stat">
                        <h3><?php echo $articles_count; ?>+</h3>
                        <p>Articles</p>
                    </div>
                    <div class="stat">
                        <h3><?php echo $users_count; ?>+</h3>
                        <p>Users</p>
                    </div>
                    <div class="stat">
                        <h3><?php echo $contributors_count; ?>+</h3>
                        <p>Contributors</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Service -->
    <?php if ($featured_service): ?>
    <section class="featured-service section__extra-margin">
        <div class="container featured-service__container">
            <div class="featured-service__image">
                <img src="./images/<?= $featured_service['thumbnail'] ?>" alt="<?= $featured_service['title'] ?>">
            </div>
            <div class="featured-service__content">
                <h2>Featured Service</h2>
                <h3><a href="post.php?id=<?= $featured_service['id'] ?>"><?= $featured_service['title'] ?></a></h3>
                <p><?= substr($featured_service['body'], 0, 300) ?>...</p>
                <div class="service-meta">
                    <div class="service-author">
                        <img src="./images/<?= $featured_service['avatar'] ?>" alt="<?= $featured_service['firstname'] ?> <?= $featured_service['lastname'] ?>">
                        <span>By <?= $featured_service['firstname'] ?> <?= $featured_service['lastname'] ?></span>
                    </div>
                    <span class="service-date"><?= date("M d, Y", strtotime($featured_service['date_time'])) ?></span>
                </div>
                <a href="post.php?id=<?= $featured_service['id'] ?>" class="btn">Read More</a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Core Services -->
    <section class="core-services section__extra-margin">
        <div class="container">
            <h2>Our Core Services</h2>
            <style>

                .stat {
                    background: var(--color-gray-200);
                    padding: 1rem;
                    border-radius: 10px;
                    text-align: center;
                    flex: 1;
                }
                .stat h3 {
                    font-size: 1.5rem;
                    color: var(--color-primary);
                }
                .stat p {
                    font-size: 0.9rem;
                    color: #666;
                }
                .services-cta__container {
                    text-align: center;
                    border: 10px solid var(--color-gray-700);
                    border-radius: 20px;
                    background: var(--color-bg);
                    box-shadow: 5px 5px 15px rgba(0,0,0,0.1);

                }
                .services-cta__container h2 {
                    color: var(--color-white);
                }
                .services-cta__container p {
                    color: var(--color-gray-700);
                    font-size: 1.1rem;
                }
                .services-cta__container .btn {
                    background: var(--color-primary);
                    color: #fff;
                    padding: 0.75rem 1.5rem;
                    border-radius: 5px;
                    transition: background 0.3s ease;
                }
                .services-cta__container .btn:hover {
                    background: var(--color-primary-dark);
                }
                .services-cta__container .btn-secondary {
                    background: var(--color-secondary);
                    color: #fff;
                }
                .services-cta__container .btn-secondary:hover {
                    background: var(--color-secondary-dark);
                }

                

                .services-hero, .featured-service {
                    background: var(--color-gray-100);
                    border-radius: 20px;
                    padding: 2rem;
                }
                .services-hero__container, .featured-service__container {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 2rem;
                    align-items: center;
                }
                .services-hero__image, .featured-service__image {
                    flex: 1 1 400px;
                    max-width: 500px;
                    border-radius: 10px;
                    overflow: hidden;
                }
                .services-hero__image img, .featured-service__image img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                }
                .services-hero__content, .featured-service__content {
                    flex: 1 1 400px;
                }
                .services-stats {
                    display: flex;
                    gap: 2rem;
                    margin-top: 1.5rem;
                }
                .services-stats .stat {
                    background: var(--color-gray-200);
                    padding: 1rem;
                    border-radius: 10px;
                    text-align: center;
                    flex: 1;
                }
                .services-stats .stat h3 {
                    font-size: 1.5rem;
                    color: var(--color-primary);
                }
                .services-stats .stat p {
                    font-size: 0.9rem;
                    color: #666;
                }
                .service-meta {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-top: 1rem;
                    font-size: 0.8rem;
                    color: #999;
                }
                .service-author {
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                }
                .service-author img {
                    width: 30px;
                    height: 30px;
                    border-radius: 50%;
                    object-fit: cover;
                }
                .service-date {
                    font-size: 0.8rem;
                    color: #999;
                }

                .container-services-hero__container, .container .featured-service__container {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 2rem;
                    align-items: center;
                    margin-top: 3rem;
                }
                .container .services-hero__image, .container .featured-service__image {
                    flex: 1 1 400px;
                    max-width: 500px;
                    border-radius: 10px;
                    overflow: hidden;
                }
                .container .services-hero__image img, .container .featured-service__image img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                }
                .container .services-hero__content, .container .featured-service__content {
                    flex: 1 1 400px;
                }
                .container .services-stats {
                    display: flex;
                    gap: 2rem;
                    margin-top: 1.5rem;
                }
                .container .services-stats .stat {
                    background: var(--color-gray-200);
                    padding: 1rem;
                    border-radius: 10px;
                    text-align: center;
                    flex: 1;
                    margin-top: 3rem;
                }
                .container .services-stats .stat h3 {
                    font-size: 1.5rem;
                    color: var(--color-primary);
                }
                .container .services-stats .stat p {
                    font-size: 0.9rem;
                    color: #666;
                }
                .container .service-meta {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-top: 1rem;
                    font-size: 0.8rem;
                    color: #999;
                }
                .container .service-author {
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                }
                .container .service-author img {
                    width: 30px;
                    height: 30px;
                    border-radius: 50%;
                    object-fit: cover;
                }
                .container .service-date {
                    font-size: 0.8rem;
                    color: #999;
                }
                .container {
                    max-width: 1200px;
                    margin: 0 auto;
                    padding: 0 1rem;
                    border: 5px solid var(--color-gray-300);
                    border-radius: 20px;
                    background: var(--color-primary);
                
                }
                .container h2 {
                    text-align: center;
                    margin-bottom: 2rem;
                    color: var(--color-white);
                }
                
                .core-services__container {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                    gap: 2rem;
                }
                .core-service-item {
                    background: var(--color-gray-900);
                    border-radius: 10px;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                    overflow: hidden;
                }
                .core-service-image {
                    width: 100%;
                    height: 200px;
                    overflow: hidden;
                    background: #f5f5f5;
                }
                .core-service-image img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                }
                .core-service-content {
                    padding: 1.5rem;
                }
                .core-service-content h3 {
                    margin-bottom: 0.5rem;
                    color: var(--color-primary);
                }
                .core-service-content p {
                    font-size: 0.9rem;
                    color: #666;
                    margin-bottom: 1rem;
                    line-height: 1.5;
                }

                .core-service-content .btn {
                    background: var(--color-primary);
                    color: #fff;
                    padding: 0.5rem 1rem;
                    border-radius: 5px;
                    transition: background 0.3s ease;
                }
                .core-service-content .btn:hover {
                    background: var(--color-primary-dark);
                }
                .core-service-content .btn-secondary {
                    background: var(--color-secondary);
                    color: #fff;
                }
                .core-service-content .btn-secondary:hover {
                    background: var(--color-secondary-dark);
                }

                .container h2 {
                    text-align: center;
                    margin-bottom: 2rem;
                    color: var(--color-white);
                }
                .container {
                    max-width: 1200px;
                    margin: 0 auto;
                    padding: 0 1rem;
                    border: 5px solid var(--color-gray-300);
                    border-radius: 20px;
                    background: var(--color-primary);
                }

                .cta-btn {
                    background: var(--color-primary);
                    color: #fff;
                    padding: 0.75rem 1.5rem;
                    border-radius: 5px;
                    transition: background 0.3s ease;
                }
                .cta-btn:hover {
                    background: var(--color-primary-dark);
                }
                .cta-btn-secondary {
                    background: var(--color-secondary);
                    color: #fff;
                }
                .cta-btn-secondary:hover {
                    background: var(--color-secondary-dark);
                }
                .cta-buttons {
                    display: flex;
                    gap: 1rem;
                    justify-content: center;
                    margin-top: 2rem;
                }

                /* Responsive fixes for shaking */
                @media (max-width: 768px) {
                    .services-hero__container, .featured-service__container {
                        flex-direction: column;
                    }
                    .services-hero__image, .featured-service__image {
                        max-width: 100%;
                    }
                    .services-stats {
                        flex-direction: column;
                        gap: 1rem;
                    }
                    .container {
                        border: none;
                        background: transparent;
                    }
                    .core-services__container {
                        grid-template-columns: 1fr;
                    }
                }

            </style>
            <div class="core-services__container">
                <!-- Virtual Museum -->
                <div class="core-service-item">
                    <div class="core-service-image">
                        <img src="./images/artifact.jpg" alt="Virtual Museum" onerror="this.src='./images/default-avatar.svg'">
                    </div>
                    <div class="core-service-content">
                        <h3>Virtual Museum</h3>
                        <p>Explore our interactive virtual museum featuring Fante artifacts, historical documents, and multimedia exhibits.</p>
                        <a href="fante-artifacts.php" class="btn" data-service="Virtual Museum">Learn More</a>
                        <?php if (isset($_SESSION['user-id']) && !isset($_SESSION['user_is_admin'])): ?>
                        <a href="user-artifacts-input.php" class="btn btn-secondary" style="margin-top: 0.5rem;">Fante Artifacts Contribution</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Research Support -->
                <div class="core-service-item">
                    <div class="core-service-image">
                        <img src="./images/" alt="Research Support" onerror="this.src='./images/default-avatar.svg'">
                    </div>
                    <div class="core-service-content">
                        <h3>Research Support</h3>
                        <p>Access scholarly resources, research papers, and academic materials related to Fante culture and linguistics.</p>
                        <a href="research-support.php" class="btn" data-service="Research Support">Support Research</a>
                    </div>
                </div>

                <!-- Community Engagement -->
                <div class="core-service-item">
                    <div class="core-service-image">
                        <img src="./images/Stuff/FanteFoods/plate.jpg" alt="Community Engagement" onerror="this.src='./images/default-avatar.svg'">
                    </div>
                    <div class="core-service-content">
                        <h3>Community Engagement</h3>
                        <p>Connect with fellow Fante culture enthusiasts, participate in discussions, and contribute to our growing knowledge base.</p>
                        <a href="fante-ceremonies.php" class="btn" data-service="Community Engagement">Join Us</a>
                        <?php if (isset($_SESSION['user-id']) && !isset($_SESSION['user_is_admin'])): ?>
                        <a href="user-ceremonies-input.php" class="btn btn-secondary" style="margin-top: 0.5rem;">Fante ceremonies contribution</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Cultural Preservation -->
                <div class="core-service-item">
                    <div class="core-service-image">
                        <img src="./images/Stuff/FanteFoods/kenkey.jpg" alt="Cultural Preservation" onerror="this.src='./images/default-avatar.svg'">
                    </div>
                    <div class="core-service-content">
                        <h3>Cultural Preservation</h3>
                        <p>Dedicated to preserving Fante traditions, history, and cultural artifacts for future generations through digital archives.</p>
                        <a href="fante-history.php" class="btn" data-service="Cultural Preservation">Discover</a>
                        <?php if (isset($_SESSION['user-id']) && !isset($_SESSION['user_is_admin'])): ?>
                        <a href="user-history-input.php" class="btn btn-secondary" style="margin-top: 0.5rem;">History Contribution</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Fante Language Learning -->
                <div class="core-service-item">
                    <div class="core-service-image">
                        <img src="./images/Stuff/FanteFoods/book1.jpg" alt="Fante Language Learning" onerror="this.src='./images/default-avatar.svg'">
                    </div>
                    <div class="core-service-content">
                        <h3>Fante Language Learning</h3>
                        <p>Comprehensive tutorials and resources for learning the Fante language, from basic alphabets to advanced conversation skills.</p>
                        <a href="fante-letter.php" class="btn" data-service="Fante Language Learning">Learn Fante Letters</a>
                    </div>
                </div>

                <!-- Fante Classroom (NEW) -->
                <div class="core-service-item">
                    <div class="core-service-image">
                        <img src="./images/Stuff/FanteFoods/book2.jpg" alt="Fante Classroom" onerror="this.src='./images/default-avatar.svg'">
                    </div>
                    <div class="core-service-content">
                        <button class="btn" style="margin-bottom:.75rem;" type="button" onclick="window.open('fante-class.php','_blank')">Fante Class</button>
                        <h3>Fante Classroom</h3>
                        <p>Admin-built quiz sessions for Beginner, Intermediate, and Advanced learners—complete questions, save progress, and get instant feedback.</p>
                    </div>
                </div>


                <!-- Fante Dictionary Contribution -->
                <div class="core-service-item">
                    <div class="core-service-image">
                        <img src="./images/Stuff/FanteFoods/book2.jpg" alt="Fante Dictionary" onerror="this.src='./images/default-avatar.svg'">
                    </div>
                    <div class="core-service-content">
                        <h3>Fante Dictionary</h3>
                        <p>Contribute to our growing Fante dictionary by submitting new words, meanings, and pronunciations. Help preserve the Fante language.</p>
                        <a href="user-contribution.php" class="btn" data-service="Fante Dictionary">Contribute to Dictionary</a>
                    </div>
                </div>

                <!-- Fante Dictionary Search -->
                <div class="core-service-item">
                    <div class="core-service-image">
                        <img src="./images/Stuff/FanteFoods/boook.jpg" alt="Fante Dictionary Search" onerror="this.src='./images/default-avatar.svg'">
                    </div>
                    <div class="core-service-content">
                        <h3>Fante Dictionary Search</h3>
                        <p>Search our comprehensive Fante dictionary for words, meanings, and pronunciations. Discover the rich Fante language.</p>
                        <a href="fante-dictionary.php" class="btn" data-service="Fante Dictionary Search">Search Dictionary</a>
                    </div>
                </div>

                <!-- Fante Phonetics -->
                <div class="core-service-item">
                    <div class="core-service-image">
                        <img src="./images/Stuff/FanteFoods/fufu.jpg" alt="Fante Phonetics" onerror="this.src='./images/default-avatar.svg'">
                    </div>
                    <div class="core-service-content">
                        <h3>Fante Phonetics</h3>
                        <p>Explore Fante phonetics through interactive videos and audio content. Learn alphabets, numbers, proverbs, and more.</p>
                        <a href="fante-phonetics.php" class="btn" data-service="Fante Phonetics">Explore Phonetics</a>
                        <?php if (isset($_SESSION['user-id']) && !isset($_SESSION['user_is_admin'])): ?>
                        <a href="user-input.php" class="btn btn-secondary" style="margin-top: 0.5rem;">User Input</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Fante States -->
                <div class="core-service-item">
                    <div class="core-service-image">
                        <img src="./images/Stuff/FanteFoods/cloth1.jpg" alt="Fante States" onerror="this.src='./images/default-avatar.svg'">
                    </div>
                    <div class="core-service-content">
                        <h3>Fante States</h3>
                        <p>Discover the rich history and culture of the 15 traditional Fante states. Explore their locations, traditions, and significance.</p>
                        <a href="fante-states.php" class="btn" data-service="Fante States">Explore Fante States</a>
                        <?php if (isset($_SESSION['user-id']) && !isset($_SESSION['user_is_admin'])): ?>
                        <a href="user-states-input.php" class="btn btn-secondary" style="margin-top: 0.5rem;">Fante State Contribution</a>
                        <?php endif; ?>
                    </div>
                </div>

<!-- Food and Cloth -->
                <div class="core-service-item">
                    <div class="core-service-image">
                        <img src="./images/Stuff/FanteFoods/fufu.jpg" alt="Food and Cloth" onerror="this.src='./images/default-avatar.svg'">
                    </div>
                    <div class="core-service-content">
                        <h3>Food and Cloth</h3>
                        <p>Explore the rich culinary traditions and traditional clothing of the Fante people. Discover authentic Fante cuisines like Fufu, Banku, and Kenkey, as well as vibrant Kente and traditional Fante attire.</p>
                        <a href="food/cloth.php" class="btn" data-service="Food and Cloth">Food/Cloth</a>
                        <?php if (isset($_SESSION['user-id']) && !isset($_SESSION['user_is_admin'])): ?>
                        <a href="food/cloth-input.php" class="btn btn-secondary" style="margin-top: 0.5rem;">Food/Cloth Contribution</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- NEW: Music and Dance -->
                <div class="core-service-item">
                    <div class="core-service-image">
                        <img src="./images/pusuban.webp" alt="Music and Dance" onerror="this.src='./images/default-avatar.svg'" style="object-fit: cover; height: 200px;">
                    </div>
                    <div class="core-service-content">
                        <h3>Music and Dance</h3>
                        <p>Discover the vibrant Fante music and dance traditions that form the heartbeat of Fante cultural celebrations and ceremonies.</p>
                        <a href="music-dance.php" class="btn" data-service="Music and Dance">Music/Dance</a>
                        <?php if (isset($_SESSION['user-id']) && !isset($_SESSION['user_is_admin'])): ?>
                        <a href="music-dance-input.php" class="btn btn-secondary" style="margin-top: 0.5rem;">Music & Dance Contribution</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="services-cta section__extra-margin">
        <div class="container services-cta__container">
            <h2>Ready to Explore Fante Culture?</h2>
            <p>Join our community of learners and contributors. Start your journey into the rich heritage of the Fante people today.</p>
            <div class="cta-buttons">
                <a href="signup.php" class="btn">Get Started</a>
                <a href="contribute.php" class="btn btn-secondary">Contribute</a>
            </div>
        </div>
    </section>

   

        <!==============================FOOTER==================================-->

   <?php
include 'partials/footer.php';
?>
