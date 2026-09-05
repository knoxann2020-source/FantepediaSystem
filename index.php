<?php
 include 'partials/header.php';
 
 // fetch all posts from database
 $featured_posts_query = "SELECT * FROM posts WHERE is_featured=1";
 $featured_posts = mysqli_query($connection, $featured_posts_query);
 $featured = mysqli_fetch_assoc($featured_posts);

 // fetch posts from tables
 $query = "SELECT * FROM posts ORDER BY date_time DESC limit 9";
    $posts = mysqli_query($connection, $query);

 // fetch approved ceremonies with videos
 $ceremonies_query = "SELECT title, video FROM fante_ceremonies WHERE status='approved' AND video IS NOT NULL AND video != ''";
 $ceremonies = mysqli_query($connection, $ceremonies_query);
 $ceremonies_array = [];
 while($ceremony = mysqli_fetch_assoc($ceremonies)) {
     $ceremonies_array[] = $ceremony;
 }

 // Fallback to online videos if no database videos
 if (empty($ceremonies_array)) {
     $ceremonies_array = [
         ['title' => 'Fante Cultural Heritage', 'video' => 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_1mb.mp4'], // Placeholder URL
         ['title' => 'Ghanaian Traditions', 'video' => 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_2mb.mp4'], // Placeholder URL
     ];
 }

 ?>

    <!-- Welcome Cover Page for New Users -->
    <div id="welcome-cover" class="welcome-cover">
        <div class="welcome-cover-content">
            <button class="welcome-cover-close" id="welcome-cover-close" title="Close">
                <i class="uil uil-times"></i>
            </button>
            
            <div class="welcome-cover-header">
                <span class="welcome-cover-icon"><i class="uil uil-book-reader"></i></span>
                <h1>Welcome to Fantepedia</h1>
                <p>Your comprehensive guide to Fante culture and heritage</p>
            </div>

            <div class="welcome-cover-section">
                <h3><i class="uil uil-search"></i> How to Use This System</h3>
                <ul>
                    <li>Browse articles and blog posts from the navigation menu</li>
                    <li>Use the search bar to find specific information</li>
                    <li>Explore categories to discover related content</li>
                    <li>Watch cultural videos in the ceremonies section</li>
                </ul>
            </div>

            <div class="welcome-cover-section">
                <h3><i class="uil uil-star"></i> Key Features</h3>
                <ul>
                    <li>Learn Fante letters and phonetics</li>
                    <li>Explore historical artifacts and states</li>
                    <li>Discover traditional ceremonies and customs</li>
                    <li>Access the Fante dictionary for translations</li>
                </ul>
            </div>

            <div class="welcome-cover-section">
                <h3><i class="uil uil-user-plus"></i> Getting Started</h3>
                <p>Create an account to contribute your own content, submit research, and participate in preserving Fante heritage. Click the Login button in the navigation to sign in or register.</p>
            </div>

            <div class="welcome-cover-footer">
                <button class="welcome-cover-btn" id="welcome-cover-btn">Get Started</button>
            </div>
        </div>
    </div>

    <script>
    // Welcome cover functionality - waits for page loader to finish
    (function() {
        var welcomeCover = document.getElementById('welcome-cover');
        var welcomeCoverClose = document.getElementById('welcome-cover-close');
        var welcomeCoverBtn = document.getElementById('welcome-cover-btn');
        
        // Function to show welcome cover
        function showWelcomeCover() {
            // Check if already seen
            if (localStorage.getItem('welcomeCoverSeen') === 'true') {
                if (welcomeCover) {
                    welcomeCover.style.display = 'none';
                }
                return;
            }
            if (welcomeCover) {
                welcomeCover.style.display = 'flex';
                // Add show class for animation
                setTimeout(function() {
                    welcomeCover.classList.add('show');
                }, 10);
            }
        }
        
        // Function to close and redirect with page loader
        function closeAndRedirect() {
            if (welcomeCover) {
                welcomeCover.classList.remove('show');
                welcomeCover.style.display = 'none';
                localStorage.setItem('welcomeCoverSeen', 'true');
                
                // Show page loader briefly then reload
                var pageLoader = document.getElementById('page-loader');
                if (pageLoader) {
                    pageLoader.style.display = 'flex';
                    setTimeout(function() {
                        window.location.reload(true);
                    }, 500);
                } else {
                    window.location.reload(true);
                }
            }
        }
        
        // Function to close only (without redirect)
        function closeOnly() {
            if (welcomeCover) {
                welcomeCover.classList.remove('show');
                welcomeCover.style.display = 'none';
                localStorage.setItem('welcomeCoverSeen', 'true');
            }
        }
        
        // Wait for page loader to be hidden, then show welcome cover
        function checkLoaderAndShow() {
            var pageLoader = document.getElementById('page-loader');
            var loaderHidden = !pageLoader || pageLoader.style.display === 'none' || getComputedStyle(pageLoader).display === 'none';
            
            if (loaderHidden) {
                showWelcomeCover();
            } else {
                // Check again in 500ms
                setTimeout(checkLoaderAndShow, 500);
            }
        }
        
        // Start checking after page load
        window.addEventListener('load', function() {
            // Small delay to ensure loader is hidden
            setTimeout(checkLoaderAndShow, 100);
        });
        
        // Add event listeners
        if (welcomeCoverClose) {
            welcomeCoverClose.addEventListener('click', closeAndRedirect);
        }
        
        if (welcomeCoverBtn) {
            welcomeCoverBtn.addEventListener('click', closeAndRedirect);
        }
        
        if (welcomeCover) {
            welcomeCover.addEventListener('click', function(e) {
                if (e.target === welcomeCover) {
                    closeOnly();
                }
            });
        }
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && welcomeCover && welcomeCover.style.display === 'flex') {
                closeOnly();
            }
        });
    })();
    </script>

    <!-- Video Background Section -->
    <?php if (!empty($ceremonies_array)): ?>
    <section class="video-background section__extra-margin">
        <video id="background-video" autoplay muted loop playsinline>
            <source src="./images/<?php echo $ceremonies_array[0]['video']; ?>" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="video-overlay">
            <div class="video-caption">
                <a href="fante-ceremonies.php" id="video-caption-link"><?php echo htmlspecialchars($ceremonies_array[0]['title']); ?></a>
            </div>
        </div>
    </section>
    <?php endif ?>

    <!..=======================================FEATURED===============================..>
    <!-- fetch posts from table if there any -->
<?php if(mysqli_num_rows($featured_posts) == 1): ?>
    <section class="featured">
        <div class="container featured__container">
            <div class="post__thumbnail">
                <img src="./images/<?php echo $featured['thumbnail']; ?>">
            </div>
            <div class="post__info">

            <!-- fetch category from categories table using category_id of post -->
            <?php
            $category_id = $featured['category_id'];
            if ($category_id) {
                $category_query = "SELECT * FROM categories WHERE id=$category_id";
                $category_result = mysqli_query($connection, $category_query);
                $category = mysqli_fetch_assoc($category_result);
            } else {
                $category = null;
            }
            ?>
                <a href="<?= ROOT_URL ?>category-posts.php?id=<?= $category ? $category['id'] : '' ?>" class="category__button"><?= $category ? $category['title'] : 'Uncategorized' ?></a>
                <h2 class="post__title"><a href="<?= ROOT_URL ?>post.php?id=<?= $featured['id'] ?>"><?= $featured['title'] ?></a></h2>
                <p class="post__body"><?= substr($featured['body'], 0, 300) ?>...
            </p>
                <div class="post__user">
                    <?php
                    // fetch user from users table using user_id of post
                    $user_id = $featured['user_id'];
                    $user_query = "SELECT * FROM users WHERE id=$user_id";
                    $user_result = mysqli_query($connection, $user_query);
                    $user = mysqli_fetch_assoc($user_result) ?: [];

                     ?>
                    <div class="post__user-avatar">
                        <img src="./images/<?= $user['avatar'] ?? 'default-avatar.png' ?>" alt="Post author">
                    </div>
                    <div class="post__user-info">
                        <h5><i>by: <?= trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')) ?: 'Unknown author' ?></i></h5>
                        <small><i>
                            <?= date("M d, Y - H:i", strtotime($featured['date_time'])) ?>
                        </i></small>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif ?>
    <!..=======================================END OF FEATURED===============================..>
    <style>
        .posts, .posts-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)) !important;
            gap: 1.5rem !important;
            border: 5px solid rgba(166, 173, 62, 0.1) !important;
            border-radius: 10px !important;
            box-shadow: inset 0 0 10px rgba(166, 173, 62, 0.1) !important;
            padding: 1.5rem !important;
            max-width: 100% !important;
            overflow: hidden !important;
        }

        @media (max-width: 1200px) {
            .posts, .posts-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }

        @media (max-width: 768px) {
            .posts, .posts-grid {
                grid-template-columns: 1fr !important;
                padding: 1rem !important;
            }
        }

        .posts .content-card,
        .posts-grid .content-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease !important;
        }

        .posts .content-card:hover,
        .posts-grid .content-card:hover {
            transform: translateY(-5px) !important;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
        }

        .modern-hero {
            grid-column: 1 / -1 !important;
            text-align: center;
            margin-bottom: 2rem;
        }
         .modern-hero h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
            .modern-hero p {
                font-size: 1.2rem;
                margin-bottom: 1.5rem;
                opacity: 0.9;
            }
            .stat-card {
                display: inline-block;
                background: rgba(255, 255, 255, 0.8);
                padding: 1rem 2rem;
                border-radius: 10px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            }
            .stat-card h3 {
                font-size: 2rem;
                margin-bottom: 0.25rem;
            }
            .stat-card p {
                font-size: 1rem;
                color: #555;
            }
            .post .content-card {
                display: grid;
                grid-template-columns: 1fr;
                border: 5px solid rgba(166, 173, 62, 0.1);
                border-radius: 10px;
                box-shadow: inset 0 0 10px rgba(166, 173, 62, 0.1);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            } 
            .post-thumbnail img {
                width: 100%;
                height: 200px;
                object-fit: cover;
                border-top-left-radius: 10px;
                border-top-right-radius: 10px;
                display: grid;
                grid-template-columns: 1fr;
            }
            .post-thumbnail :hover img {
                filter: brightness(1.1);
                box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            }
             .post__info {
                padding: 1rem;
            }
             .post__title {
                font-size: 1.5rem;
                margin-bottom: 0.5rem;
            }
             .post__body {
                font-size: 1rem;
                color: #918d8dce;
                margin-bottom: 1rem;
            }
             .post__user {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }
             .post__user-avatar img {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                object-fit: cover;
            }
             .post__user-info h5 {
                margin: 0;
                font-size: 0.9rem;
            }
             .post__user-info small {
                color: #999292ec;
            }  
    </style>
<section class="dashboard posts <?= $featured ? '' : 'section__extra-margin' ?> modern-grid">
     <div class="container posts-grid">
<div class="modern-hero" style="grid-column: 1 / -1;">
            <h1>Latest Posts</h1>
            <p>Discover the most recent articles and updates about Fante culture</p>
            <div class="stat-card">
                <h3><?= mysqli_num_rows($posts) ?></h3>
                <p>Recent Articles</p>
            </div>
        </div>
            <?php while($post = mysqli_fetch_assoc($posts)) : ?>
           
        <article class="post content-card">
                <div class="post__thumbnail">
                    <img src="./images/<?= $post['thumbnail'] ?>">
                </div>
                <div class="post__info">
                     <!-- fetch category from categories table using category_id of post -->
            <?php
            $category_id = $post['category_id'];
            if ($category_id) {
                $category_query = "SELECT * FROM categories WHERE id=$category_id";
                $category_result = mysqli_query($connection, $category_query);
                $category = mysqli_fetch_assoc($category_result);
            } else {
                $category = null;
            }
            ?>
                    <a href="<?= ROOT_URL ?>category-posts.php?id=<?= $category ? $category['id'] : '' ?>"
                    class="category__button"><?= $category ? $category['title'] : 'Uncategorized' ?></a>
                    <h3 class="post__title">
                        <a href="post.php?id=<?= $post['id'] ?>"><?= $post['title'] ?></a>
                    </h3>
                    <p class="post__body">
                       <?= substr($post['body'], 0, 150) ?>...
            </p>
                    <div class="post__user">
                        <div class="post__user-avatar">
                            <?php
                            // fetch user from users table using user_id of post
                            $user_id = $post['user_id'];
                            $user_query = "SELECT * FROM users WHERE id=$user_id";
                            $user_result = mysqli_query($connection, $user_query);
                            $user = mysqli_fetch_assoc($user_result) ?: [];

                             ?>
                            <img src="./images/<?= $user['avatar'] ?? 'default-avatar.png' ?>" alt="Post author">
                        </div>
                        <div class="post__user-info">
                           <h5><i>by: <?= trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')) ?: 'Unknown author' ?></i></h5>
                            <small><i>
                                <?= date("M d, Y - H:i", strtotime($post['date_time'])) ?>
                            </i></small>
                        </div>
                    </div>
                </div>
            </article>
            <?php endwhile ?>
        </div>

    </section>

    <!-======================================END POST================================>

    <section class="category__button">
        <div class="container category__button-container">
            <?php 
            $all_categories_query = "SELECT * FROM categories";
            $all_categories = mysqli_query($connection, $all_categories_query);
            
            ?>
            <?php while($category = mysqli_fetch_assoc($all_categories)) : ?>
            <a href="<?= ROOT_URL ?>category-posts.php?id=<?= $category['id'] ?>" 
            class="category__button"><?= $category['title'] ?></a>

                <?php endwhile ?>
            </div>
    </section>

    <!-- Testimonials (replaced from contact.php) -->
    <section class="testimonials contact-section-animate">
        <h2>What People Say</h2>
        <div class="testimonial-grid">
            <div class="testimonial-card">
                <div class="testimonial-avatar">
                    <img src="images/default-avatar.svg" alt="Kwame Appiah">
                </div>
                <p class="testimonial-text">"Fantepedia has been an incredible resource for learning Fante. The contact team is very responsive and helpful!"</p>
                <div class="testimonial-author">Kwame Appiah</div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-avatar">
                    <img src="images/default-avatar.svg" alt="Abena Mensah">
                </div>
                <p class="testimonial-text">"Quick responses and excellent support. Perfect for anyone serious about learning Fante culture and language."</p>
                <div class="testimonial-author">Abena Mensah</div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-avatar">
                    <img src="images/default-avatar.svg" alt="Kofi Boateng">
                </div>
                <p class="testimonial-text">"The resources are comprehensive and the support team helped me get started quickly. Highly recommended!"</p>
                <div class="testimonial-author">Kofi Boateng</div>
            </div>
        </div>
    </section>

    <!..==================================END OF CATEGORY BUTTON==============================>


   



    <script>
        <?php if (!empty($ceremonies_array)): ?>
        const ceremonies = <?php echo json_encode($ceremonies_array); ?>;
        let currentIndex = 0;
        const videoElement = document.getElementById('background-video');
        const captionLink = document.getElementById('video-caption-link');

        function shuffleVideo() {
            currentIndex = (currentIndex + 1) % ceremonies.length;
            const nextCeremony = ceremonies[currentIndex];

            // Fade out current video
            videoElement.style.opacity = '0';

            setTimeout(() => {
                const videoSrc = nextCeremony.video.startsWith('http') ? nextCeremony.video : './images/' + nextCeremony.video;
                videoElement.src = videoSrc;
                captionLink.textContent = nextCeremony.title;
                videoElement.load();
                videoElement.play();

                // Fade in new video
                videoElement.style.opacity = '1';
            }, 500);
        }

        // Shuffle every 10 seconds
        setInterval(shuffleVideo, 10000);
        <?php endif; ?>

        // Testimonials animation observer
        document.addEventListener('DOMContentLoaded', function() {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate');
                    }
                });
            }, observerOptions);
            
            const testimonialsSection = document.querySelector('.testimonials');
            if (testimonialsSection) observer.observe(testimonialsSection);
        });
    </script>


 <style>
    .learning-tools {
        background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-variant) 100%);
        color: white;
        padding: 4rem 0;
        text-align: center;
    }

    .learning-tools__container {
        max-width: 800px;
        margin: 0 auto;
    }

    .learning-tools h2 {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        animation: fadeInDown 0.8s ease-out;
    }

    .learning-tools p {
        font-size: 1.2rem;
        margin-bottom: 2rem;
        opacity: 0.9;
    }

    .learning-tools .category__button {
        background: white;
        color: var(--color-primary);
        border: 2px solid white;
        padding: 1rem 2rem;
        font-size: 1.1rem;
        font-weight: bold;
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-block;
        margin: 0 auto;
    }

    .learning-tools .category__button:hover {
        background: var(--color-secondary);
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.2);
    }

    @keyframes fadeInDown {
        from { transform: translateY(-30px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    @keyframes slideDown {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    @keyframes slideUp {
        from { transform: translateY(0); opacity: 1; }
        to { transform: translateY(-20px); opacity: 0; }
    }

    #fante-alphabet-section {
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        overflow: hidden;
    } 

    
    .video-background-section {
        position: relative;
        width: 100%;
        height: 60vh;
        overflow: hidden;
        margin-bottom: 2rem;

    }

    .video-background-section video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 100;
        transition: opacity 0.5s ease-in-out;

    }

    .video-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(116, 105, 105, 0.72);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 60px;
        box-shadow: 4px 4px 15px rgba(0,0,0,0.3);
        border: 5px solid rgba(255, 255, 255, 0.8);
        object-fit: cover;



    }

    .video-caption {
        text-align: center;
    }

    .video-caption a {
        color: white;
        font-size: 2rem;
        font-weight: bold;
        text-decoration: none;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.7);
        transition: color 0.3s ease;
    }

    .video-caption a:hover {
        color: #ffd700;
    }

    .testimony__container {
        max-width: 800px;
        margin: 0 auto;
        border: 5px solid rgba(166, 173, 62, 0.1);
        border-radius: 10px;
        padding: 2rem;
        background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-variant) 100%);
        color: white;
        box-shadow: 4px 4px 15px rgba(0,0,0,0.3);

    }

    .form__group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: bold;
    }
    .form__group input,
    .form__group textarea {
        width: 100%;
        padding: 0.75rem;
        border: none;
        border-radius: 5px;
        margin-bottom: 1rem;
    }
    .testimony-submit {
        background: white;
        color: var(--color-primary);
        border: 2px solid white;
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
        font-weight: bold;
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-bottom: 1rem;

    }   
    .testimony-submit:hover {
        background: var(--color-secondary);
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.2);
    }

    .form__group textarea {
        resize: vertical;
        min-height: 100px;
    }
    .form__group input:focus,
    .form__group textarea:focus {
        outline: none;
        box-shadow: 0 0 5px rgba(255, 255, 255, 0.8);
    }
    .no__testimonies {
        text-align: center;
        font-style: italic;
        color: rgba(255, 255, 255, 0.8);
    }
    .no__testimonies p {
        margin-top: 1rem;
    }


     .testimony__item {
        display: flex;
        align-items: flex-start;
        margin-bottom: 1.5rem;
        background: rgba(255, 255, 255, 0.1);
        padding: 1rem;
        border-radius: 10px;
    }
    .testimony__avatar {
        margin-right: 1rem;
    }
    .testimony__avatar img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
    }
    .testimony__content {
        flex: 1;
    }
    .testimony__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    .testimony__message {
        font-size: 1rem;
        line-height: 1.5;
    }
    .testimony-alert {
        margin-bottom: 1rem;
        border-radius: 5px;
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
    }
    .testimony-alert.success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .testimony-alert.error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    .testimony-alert p {
        margin: 0;
    }
    .testimony-alert.success p {
        color: #155724;
    }
    .testimony-alert.error p {
        color: #721c24;
    }
    .testimony-alert.success:hover {
        background: #c3e6cb;
        color: #155724;
        border: 1px solid #155724;
    }
    .testimony-alert.error:hover {
        background: #f5c6cb;
        color: #721c24;
        border: 1px solid #721c24;
    }
    .testimony-list {
        margin-top: 2rem;
    }
    

 </style>



<?php
include 'partials/footer.php';
?>



 