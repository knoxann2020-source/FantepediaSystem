<?php
session_start();
require 'config/constants.php';
require 'config/database.php';

include 'partials/header.php';

// Get filter/search parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : 'all';

// Fetch research categories from database
$categories_query = "SELECT DISTINCT category_id, c.title as category_title 
                     FROM posts p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     WHERE c.title IS NOT NULL 
                     ORDER BY c.title";
$categories_result = mysqli_query($connection, $categories_query);
$categories = mysqli_fetch_all($categories_result, MYSQLI_ASSOC);

// Fetch research posts from database
if ($search) {
    $research_query = "SELECT p.*, u.username, u.avatar, c.title as category 
                       FROM posts p 
                       LEFT JOIN users u ON p.user_id = u.id 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       WHERE (p.title LIKE '%$search%' OR p.body LIKE '%$search%' OR p.tags LIKE '%$search%')
                       ORDER BY p.created_at DESC LIMIT 50";
} elseif ($category && $category !== 'all') {
    $research_query = "SELECT p.*, u.username, u.avatar, c.title as category 
                       FROM posts p 
                       LEFT JOIN users u ON p.user_id = u.id 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       WHERE c.title = '$category'
                       ORDER BY p.created_at DESC LIMIT 50";
} else {
    $research_query = "SELECT p.*, u.username, u.avatar, c.title as category 
                       FROM posts p 
                       LEFT JOIN users u ON p.user_id = u.id 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       ORDER BY p.created_at DESC LIMIT 50";
}

$research_result = mysqli_query($connection, $research_query);
$research_posts = mysqli_fetch_all($research_result, MYSQLI_ASSOC);

// Define external research links (automated online links)
$external_research_links = [
    [
        'title' => 'Fante People - Wikipedia',
        'description' => 'Comprehensive encyclopedia article on the Fante people, their history, culture, and social structure.',
        'url' => 'https://en.wikipedia.org/wiki/Fante_people',
        'category' => 'History'
    ],
    [
        'title' => 'Fante Language - Ethnologue',
        'description' => 'Detailed linguistic information about the Fante language, including dialect details and speaking populations.',
        'url' => 'https://www.ethnologue.com/language/fat',
        'category' => 'Language'
    ],
    [
        'title' => 'Fante Confederacy - Historical Research',
        'description' => 'Academic research on the Fante Confederacy and its political significance in Ghanaian history.',
        'url' => 'https://en.wikipedia.org/wiki/Fante_Confederacy',
        'category' => 'Politics'
    ],
    [
        'title' => 'Fante Traditional States',
        'description' => 'Research on the traditional Fante states and their governance systems.',
        'url' => 'https://en.wikipedia.org/wiki/Fante_states',
        'category' => 'Politics'
    ],
    [
        'title' => 'Fante Music and Dance',
        'description' => 'Scholarly article on traditional Fante music, dance forms, and cultural performances.',
        'url' => 'https://www.britannica.com/topic/music-dance',
        'category' => 'Culture'
    ],
    [
        'title' => 'Fante Cuisine and Food Culture',
        'description' => 'Research on traditional Fante dishes, food preparation, and culinary heritage.',
        'url' => 'https://en.wikipedia.org/wiki/Ghanaian_cuisine',
        'category' => 'Culture'
    ],
    [
        'title' => 'Fante Religious Practices',
        'description' => 'Academic research on traditional Fante religious beliefs and spiritual practices.',
        'url' => 'https://en.wikipedia.org/wiki/African_traditional_religion',
        'category' => 'Religion'
    ],
    [
        'title' => 'Fante Trade and Economy',
        'description' => 'Historical research on Fante trading networks and economic activities along the Gold Coast.',
        'url' => 'https://en.wikipedia.org/wiki/Gold_Coast_(region)',
        'category' => 'Economy'
    ],
    [
        'title' => 'Fante Literature and Oral Tradition',
        'description' => 'Research on Fante oral traditions, proverbs, storytelling, and literary heritage.',
        'url' => 'https://en.wikipedia.org/wiki/Oral_tradition',
        'category' => 'Literature'
    ],
    [
        'title' => 'Fante Art and Craftsmanship',
        'description' => 'Study of traditional Fante artistic expressions, crafts, and material culture.',
        'url' => 'https://en.wikipedia.org/wiki/African_art',
        'category' => 'Art'
    ]
];

// Filter external links based on search
if ($search) {
    $external_research_links = array_filter($external_research_links, function($link) use ($search) {
        return stripos($link['title'], $search) !== false || 
               stripos($link['description'], $search) !== false ||
               stripos($link['category'], $search) !== false;
    });
}

// Filter external links by category
if ($category && $category !== 'all') {
    $external_research_links = array_filter($external_research_links, function($link) use ($category) {
        return stripos($link['category'], $category) !== false;
    });
}

// Define video links
$video_links = [
    [
        'title' => 'Introduction to Fante Culture',
        'description' => 'A comprehensive video introduction to Fante culture and traditions.',
        'url' => 'https://www.youtube.com/watch?v=example1',
        'thumbnail' => 'https://img.youtube.com/vi/example1/hqdefault.jpg'
    ],
    [
        'title' => 'Fante Language Basics',
        'description' => 'Learn the fundamentals of the Fante language with this educational video.',
        'url' => 'https://www.youtube.com/watch?v=example2',
        'thumbnail' => 'https://img.youtube.com/vi/example2/hqdefault.jpg'
    ],
    [
        'title' => 'Fante Traditional Ceremonies',
        'description' => 'Documentary on traditional Fante ceremonies and celebrations.',
        'url' => 'https://www.youtube.com/watch?v=example3',
        'thumbnail' => 'https://img.youtube.com/vi/example3/hqdefault.jpg'
    ],
    [
        'title' => 'Fante Kente Weaving',
        'description' => 'Traditional Kente cloth weaving techniques of the Fante people.',
        'url' => 'https://www.youtube.com/watch?v=example4',
        'thumbnail' => 'https://img.youtube.com/vi/example4/hqdefault.jpg'
    ],
    [
        'title' => 'Fante Music and Drumming',
        'description' => 'Traditional Fante music and drumming performances.',
        'url' => 'https://www.youtube.com/watch?v=example5',
        'thumbnail' => 'https://img.youtube.com/vi/example5/hqdefault.jpg'
    ],
    [
        'title' => 'Fante History Documentary',
        'description' => 'Historical documentary on the Fante people and their journey.',
        'url' => 'https://www.youtube.com/watch?v=example6',
        'thumbnail' => 'https://img.youtube.com/vi/example6/hqdefault.jpg'
    ]
];

// Count total research items
$total_posts = count($research_posts);
$total_external = count($external_research_links);
$total_videos = count($video_links);
?>

<style>
/* Research Support Page Specific Styles */
.research-hero {
    background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-purple) 100%);
    padding: 4rem 2rem;
    margin-top: 6rem;
    border-radius: var(--card-border-radius-5);
    text-align: center;
    color: white;
    position: relative;
    overflow: hidden;
}

.research-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text y="50%" font-size="50">📚</text></svg>') repeat;
    opacity: 0.1;
    pointer-events: none;
}

.research-hero h1 {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: white;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.research-hero p {
    font-size: 1.2rem;
    opacity: 0.9;
    max-width: 600px;
    margin: 0 auto 2rem;
}

.research-stats {
    display: flex;
    justify-content: center;
    gap: 3rem;
    flex-wrap: wrap;
    margin-top: 2rem;
}

.research-stat {
    background: rgba(255,255,255,0.2);
    padding: 1.5rem 2rem;
    border-radius: var(--card-border-radius-3);
    backdrop-filter: blur(10px);
    transition: var(--transition);
}

.research-stat:hover {
    transform: translateY(-5px);
    background: rgba(255,255,255,0.3);
}

.research-stat h3 {
    font-size: 2.5rem;
    color: white;
    margin: 0;
}

.research-stat p {
    margin: 0;
    font-size: 0.9rem;
    color: rgba(255,255,255,0.9);
}

/* Search and Filter Section */
.research-controls {
    background: var(--color-gray-900);
    padding: 2rem;
    margin: 2rem 0;
    border-radius: var(--card-border-radius-3);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.research-search-form {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    justify-content: center;
}

.research-search-form input {
    flex: 1;
    min-width: 250px;
    padding: 0.8rem 1.5rem;
    border: 2px solid var(--color-gray-700);
    border-radius: var(--card-border-radius-3);
    background: var(--color-gray-700);
    color: white;
    font-size: 1rem;
}

.research-search-form input:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 10px rgba(111, 106, 248, 0.3);
}

.research-search-form select {
    padding: 0.8rem 1.5rem;
    border: 2px solid var(--color-gray-700);
    border-radius: var(--card-border-radius-3);
    background: var(--color-gray-700);
    color: white;
    font-size: 1rem;
    cursor: pointer;
}

.research-search-form button {
    padding: 0.8rem 2rem;
    background: var(--color-primary);
    color: white;
    border: none;
    border-radius: var(--card-border-radius-3);
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
}

.research-search-form button:hover {
    background: var(--color-primary-variant);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(111, 106, 248, 0.4);
}

/* Tabs Navigation */
.research-tabs {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin: 2rem 0;
    flex-wrap: wrap;
}

.research-tab {
    padding: 1rem 2rem;
    background: var(--color-gray-900);
    color: white;
    border: none;
    border-radius: var(--card-border-radius-3);
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
}

.research-tab:hover {
    background: var(--color-gray-700);
}

.research-tab.active {
    background: var(--color-primary);
    box-shadow: 0 4px 15px rgba(111, 106, 248, 0.4);
}

.research-content {
    display: none;
    animation: fadeIn 0.5s ease;
}

.research-content.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Research Posts Section */
.posts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 2rem;
    margin: 2rem 0;
}

.research-post-card {
    background: var(--color-gray-900);
    border-radius: var(--card-border-radius-3);
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    transition: var(--transition);
    display: flex;
    flex-direction: column;
}

.research-post-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.3);
}

.research-post-card .post-thumbnail {
    height: 200px;
    overflow: hidden;
    position: relative;
}

.research-post-card .post-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.research-post-card:hover .post-thumbnail img {
    transform: scale(1.1);
}

.research-post-card .post-category {
    position: absolute;
    top: 1rem;
    left: 1rem;
    background: var(--color-primary);
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: var(--card-border-radius-2);
    font-size: 0.8rem;
    font-weight: 600;
}

.research-post-card .post-content {
    padding: 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.research-post-card .post-title {
    font-size: 1.3rem;
    margin-bottom: 0.8rem;
    color: var(--color-primary);
}

.research-post-card .post-title a {
    color: inherit;
    text-decoration: none;
    transition: var(--transition);
}

.research-post-card .post-title a:hover {
    color: var(--color-purple);
}

.research-post-card .post-excerpt {
    color: var(--color-gray-200);
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 1rem;
    flex: 1;
}

.research-post-card .post-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 1px solid var(--color-gray-700);
    font-size: 0.85rem;
    color: var(--color-gray-300);
}

.research-post-card .post-author {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.research-post-card .post-author img {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    object-fit: cover;
}

/* External Links Section */
.external-links-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin: 2rem 0;
}

.external-link-card {
    background: var(--color-gray-900);
    border-radius: var(--card-border-radius-3);
    padding: 1.5rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    transition: var(--transition);
    border-left: 4px solid var(--color-primary);
}

.external-link-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.3);
    border-left-color: var(--color-purple);
}

.external-link-card .link-category {
    display: inline-block;
    background: var(--color-purple);
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: var(--card-border-radius-2);
    font-size: 0.75rem;
    font-weight: 600;
    margin-bottom: 1rem;
    text-transform: uppercase;
}

.external-link-card h3 {
    font-size: 1.2rem;
    margin-bottom: 0.8rem;
    color: var(--color-primary);
}

.external-link-card h3 a {
    color: inherit;
    text-decoration: none;
    transition: var(--transition);
}

.external-link-card h3 a:hover {
    color: var(--color-purple);
}

.external-link-card p {
    color: var(--color-gray-200);
    font-size: 0.9rem;
    line-height: 1.5;
    margin-bottom: 1rem;
}

.external-link-card .link-button {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.2rem;
    background: var(--color-primary);
    color: white;
    border-radius: var(--card-border-radius-2);
    font-size: 0.9rem;
    font-weight: 600;
    transition: var(--transition);
}

.external-link-card .link-button:hover {
    background: var(--color-primary-variant);
    transform: translateX(5px);
}

/* Videos Section */
.videos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 2rem;
    margin: 2rem 0;
}

.video-card {
    background: var(--color-gray-900);
    border-radius: var(--card-border-radius-3);
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    transition: var(--transition);
}

.video-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.3);
}

.video-card .video-thumbnail {
    position: relative;
    height: 180px;
    overflow: hidden;
}

.video-card .video-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.video-card:hover .video-thumbnail img {
    transform: scale(1.1);
}

.video-card .play-button {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 60px;
    height: 60px;
    background: rgba(255,255,255,0.9);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
    cursor: pointer;
}

.video-card .play-button::before {
    content: '';
    border-style: solid;
    border-width: 10px 0 10px 20px;
    border-color: transparent transparent transparent var(--color-primary);
    margin-left: 5px;
}

.video-card:hover .play-button {
    background: var(--color-primary);
    transform: translate(-50%, -50%) scale(1.1);
}

.video-card:hover .play-button::before {
    border-color: transparent transparent transparent white;
}

.video-card .video-content {
    padding: 1.5rem;
}

.video-card .video-title {
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
    color: var(--color-primary);
}

.video-card .video-title a {
    color: inherit;
    text-decoration: none;
    transition: var(--transition);
}

.video-card .video-title a:hover {
    color: var(--color-purple);
}

.video-card .video-description {
    color: var(--color-gray-200);
    font-size: 0.9rem;
    line-height: 1.5;
}

/* No Results Message */
.no-results {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--color-gray-200);
}

.no-results i {
    font-size: 4rem;
    margin-bottom: 1rem;
    color: var(--color-gray-700);
}

.no-results h3 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: var(--color-primary);
}

.no-results p {
    font-size: 1rem;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
    border: 5px solid var(--color-primary);
    border-radius: var(--card-border-radius-4);
    box-shadow: 5px 5px 20px rgba(111, 106, 248, 0.4);
    background: var(--color-bg);
}



/* Loading Animation */
.loading-spinner {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 4rem;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 4px solid var(--color-gray-700);
    border-top: 4px solid var(--color-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

/* Contribution Call-to-Action */
.contribution-cta {
    background: var(--color-primary);
    color: white;
    padding: 2rem;
    border-radius: var(--card-border-radius-3);
    text-align: center;
    margin: 3rem 0;
    box-shadow: 0 4px 15px rgba(111, 106, 248, 0.4);
}
.contribution-cta h2 {
    font-size: 2rem;
    margin-bottom: 1rem;
}
.contribution-cta p {
    font-size: 1.2rem;
    margin-bottom: 1.5rem;
}
.contribution-cta a {
    padding: 0.8rem 2rem;
    background: white;
    color: var(--color-primary);
    border-radius: var(--card-border-radius-2);
    font-size: 1rem;
    font-weight: 600;
    transition: var(--transition);
}
.contribution-cta a:hover {
    background: var(--color-primary-variant);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(111, 106, 248, 0.4);
}

/* Contribution Form */
.contribution-form {
    background: var(--color-gray-900);
    padding: 2rem;
    border-radius: var(--card-border-radius-3);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    margin: 2rem 0;
}
.contribution-form h2 {
    font-size: 1.8rem;
    margin-bottom: 1rem;
    color: var(--color-primary);
}
.contribution-form form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.contribution-form input, .contribution-form textarea {
    padding: 0.8rem 1.5rem;
    border: 2px solid var(--color-gray-700);
    border-radius: var(--card-border-radius-3);
    background: var(--color-gray-700);
    color: white;
    font-size: 1rem;
}
.contribution-form input:focus, .contribution-form textarea:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 10px rgba(111, 106, 248, 0.3);
}
.contribution-form button {
    padding: 0.8rem 2rem;
    background: var(--color-primary);
    color: white;
    border: none;
    border-radius: var(--card-border-radius-3);
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
}
.contribution-form button:hover {
    background: var(--color-primary-variant);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(111, 106, 248, 0.4);
}

/* Contribution Success Message */
.contribution-success {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--color-gray-200);
}
.contribution-success i {
    font-size: 4rem;
    margin-bottom: 1rem;
    color: var(--color-primary);
}
.contribution-success h3 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: var(--color-primary);
}
.contribution-success p {
    font-size: 1rem;
}

/* Contribution Error Message */
.contribution-error {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--color-gray-200);
}
.contribution-error i {
    font-size: 4rem;
    margin-bottom: 1rem;
    color: var(--color-red);
}
.contribution-error h3 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: var(--color-red);
}
.contribution-error p {
    font-size: 1rem;
}

/* Contribution Form Validation Errors */
.contribution-form .error-message {
    color: var(--color-red);
    font-size: 0.9rem;
    margin-top: -0.5rem;
    margin-bottom: 0.5rem;
}

/* Contribution Container */
.contribution-container {
    max-width: 600px;
    margin: 0 auto;
}

.contribution-container .contribution-form {
    margin-top: 2rem;
}

.services-cta__container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
    border-radius: var(--card-border-radius-4);
    box-shadow: 4px 4px 15px rgba(111, 106, 248, 0.4);
    border: 5px solid var(--color-primary);
    background: var(--color-gray-900);
}
.services-cta__container h2 {
    font-size: 2rem;
    margin-bottom: 1rem;
    color: var(--color-primary);
}
.services-cta__container p {
    font-size: 1.2rem;
    margin-bottom: 1.5rem;
    color: var(--color-gray-200);
}
.services-cta__container a {
    padding: 0.8rem 2rem;
    background: var(--color-primary);
    color: white;
    border-radius: var(--card-border-radius-2);
    font-size: 1rem;
    font-weight: 300;
    transition: var(--transition);
    margin-bottom: 2rem;
}
.services-cta__container a:hover {
    background: var(--color-primary-variant);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(111, 106, 248, 0.4);
}



/* Accessibility Focus Styles */
.research-search-form input:focus, .research-search-form select:focus, .research-search-form button:focus,
.research-tab:focus, .research-post-card .post-title a:focus, .external-link-card h3 a:focus, .external-link-card .link-button:focus,
.video-card .video-title a:focus, .video-card .play-button:focus {
    outline: 2px solid var(--color-primary);
    outline-offset: 2px;
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
    .research-hero {
        background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-purple) 100%);
    }
    
    .research-controls {
        background: var(--color-gray-900);
    }
    
    .research-search-form input,
    .research-search-form select {
        background: var(--color-gray-700);
        border-color: var(--color-gray-700);
        color: white;
    }
    
    .research-search-form button {
        background: var(--color-primary);
        color: white;
    }
    
    .research-tab {
        background: var(--color-gray-900);
        color: white;
    }
    
    .research-tab.active {
        background: var(--color-primary);
    }
    
    .research-post-card {
        background: var(--color-gray-900);
    }
    
    .external-link-card {
        background: var(--color-gray-900);
        border-left-color: var(--color-primary);
    }
    
    .video-card {
        background: var(--color-gray-900);
    }
}



/* CSS Keyframes for Spinner Animation */

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 768px) {
    .research-hero h1 {
        font-size: 2rem;
    }
    
    .research-stats {
        gap: 1rem;
    }
    
    .research-stat {
        padding: 1rem 1.5rem;
    }
    
    .research-stat h3 {
        font-size: 1.8rem;
    }
    
    .posts-grid,
    .external-links-grid,
    .videos-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .research-hero {
        padding: 2rem 1rem;
    }
    
    .research-hero h1 {
        font-size: 1.8rem;
    }
    
    .research-controls {
        padding: 1rem;
    }
    
    .research-tab {
        padding: 0.8rem 1.2rem;
        font-size: 0.9rem;
    }
}
</style>

<!-- Research Hero Section -->
<section class="research-hero">
    <div class="container">
        <h1>📚 Research Support Center</h1>
        <p>Access comprehensive research materials, scholarly articles, and multimedia resources on Fante culture and heritage.</p>
        
        <div class="research-stats">
            <div class="research-stat">
                <h3><?php echo $total_posts; ?>+</h3>
                <p>Research Papers</p>
            </div>
            <div class="research-stat">
                <h3><?php echo $total_external; ?>+</h3>
                <p>External Resources</p>
            </div>
            <div class="research-stat">
                <h3><?php echo $total_videos; ?>+</h3>
                <p>Video Resources</p>
            </div>
        </div>
    </div>
</section>

<!-- Search and Filter Section -->
<section class="research-controls">
    <div class="container">
        <form class="research-search-form" method="GET" action="">
            <input type="text" name="search" placeholder="Search research papers, topics, or keywords..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="category">
                <option value="all">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat['category_title']); ?>" <?php echo $category === $cat['category_title'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['category_title']); ?>
                    </option>
                <?php endforeach; ?>
                <option value="History" <?php echo $category === 'History' ? 'selected' : ''; ?>>History</option>
                <option value="Culture" <?php echo $category === 'Culture' ? 'selected' : ''; ?>>Culture</option>
                <option value="Language" <?php echo $category === 'Language' ? 'selected' : ''; ?>>Language</option>
                <option value="Politics" <?php echo $category === 'Politics' ? 'selected' : ''; ?>>Politics</option>
                <option value="Religion" <?php echo $category === 'Religion' ? 'selected' : ''; ?>>Religion</option>
                <option value="Economy" <?php echo $category === 'Economy' ? 'selected' : ''; ?>>Economy</option>
                <option value="Literature" <?php echo $category === 'Literature' ? 'selected' : ''; ?>>Literature</option>
                <option value="Art" <?php echo $category === 'Art' ? 'selected' : ''; ?>>Art</option>
            </select>
            <button type="submit"><i class="uil uil-search"></i> Search</button>
            <?php if ($search || ($category && $category !== 'all')): ?>
                <a href="research-support.php" class="btn" style="padding: 0.8rem 1.5rem; background: var(--color-red);">Clear</a>
            <?php endif; ?>
        </form>
    </div>
</section>

<!-- Tabs Navigation -->
<div class="research-tabs">
    <button class="research-tab active" data-tab="posts">Research Papers</button>
    <button class="research-tab" data-tab="external">External Resources</button>
    <button class="research-tab" data-tab="videos">Video Resources</button>
</div>

<!-- Research Posts Content -->
<div id="posts" class="research-content active">
    <section class="posts">
        <div class="container">
            <?php if (!empty($research_posts)): ?>
                <div class="posts-grid">
                    <?php foreach ($research_posts as $post): ?>
                        <article class="research-post-card">
                            <div class="post-thumbnail">
                                <img src="./images/<?php echo $post['thumbnail']; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" onerror="this.src='./images/default-avatar.svg'">
                                <span class="post-category"><?php echo htmlspecialchars($post['category'] ?: 'Uncategorized'); ?></span>
                            </div>
                            <div class="post-content">
                                <h3 class="post-title">
                                    <a href="post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a>
                                </h3>
                                <p class="post-excerpt"><?php echo htmlspecialchars(substr($post['body'], 0, 150)) . '...'; ?></p>
                                <div class="post-meta">
                                    <div class="post-author">
                                        <img src="./images/<?php echo $post['avatar']; ?>" alt="<?php echo htmlspecialchars($post['username']); ?>" onerror="this.src='./images/default-avatar.svg'">
                                        <span><?php echo htmlspecialchars($post['username']); ?></span>
                                    </div>
                                    <span class="post-date"><?php echo date("M d, Y", strtotime($post['created_at'])); ?></span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <i class="uil uil-search"></i>
                    <h3>No Research Papers Found</h3>
                    <p>Try adjusting your search or browse all categories.</p>
                    <a href="research-support.php" class="btn" style="margin-top: 1rem;">View All Research</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<!-- External Links Content -->
<div id="external" class="research-content">
    <section>
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 1rem;">🔗 External Research Resources</h2>
            <p style="text-align: center; color: var(--color-gray-200); margin-bottom: 2rem;">Access authoritative external research materials on Fante culture from trusted sources.</p>
            
            <?php if (!empty($external_research_links)): ?>
                <div class="external-links-grid">
                    <?php foreach ($external_research_links as $link): ?>
                        <div class="external-link-card">
                            <span class="link-category"><?php echo htmlspecialchars($link['category']); ?></span>
                            <h3><a href="<?php echo $link['url']; ?>" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($link['title']); ?></a></h3>
                            <p><?php echo htmlspecialchars($link['description']); ?></p>
                            <a href="<?php echo $link['url']; ?>" target="_blank" rel="noopener noreferrer" class="link-button">
                                Access Resource <i class="uil uil-external-link-alt"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <i class="uil uil-link-broken"></i>
                    <h3>No External Resources Found</h3>
                    <p>Try adjusting your search criteria.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<!-- Videos Content -->
<div id="videos" class="research-content">
    <section>
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 1rem;">🎬 Video Resources</h2>
            <p style="text-align: center; color: var(--color-gray-200); margin-bottom: 2rem;">Watch documentaries, tutorials, and cultural presentations on Fante heritage.</p>
            
            <div class="videos-grid">
                <?php foreach ($video_links as $video): ?>
                    <div class="video-card">
                        <div class="video-thumbnail">
                            <img src="<?php echo $video['thumbnail']; ?>" alt="<?php echo htmlspecialchars($video['title']); ?>" onerror="this.src='./images/default-avatar.svg'">
                            <div class="play-button" onclick="window.open('<?php echo $video['url']; ?>', '_blank')"></div>
                        </div>
                        <div class="video-content">
                            <h3 class="video-title">
                                <a href="<?php echo $video['url']; ?>" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($video['title']); ?></a>
                            </h3>
                            <p class="video-description"><?php echo htmlspecialchars($video['description']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>

<!-- Call to Action Section -->
<section class="services-cta" style="margin-top: 3rem;">
    <div class="container services-cta__container">
        <h2>Contribute to Research</h2>
        <p>Help preserve Fante culture by contributing your knowledge and research materials to our growing database.</p>
        <div class="cta-buttons">
            <?php if (isset($_SESSION['user-id'])): ?>
                <a href="research-submit.php" class="btn">Submit Research</a>
                <a href="contribute.php" class="btn btn-secondary">Learn More</a>
            <?php else: ?>
                <a href="signup.php" class="btn">Join Community</a>
                <a href="signin.php" class="btn btn-secondary">Sign In</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching functionality
    const tabs = document.querySelectorAll('.research-tab');
    const contents = document.querySelectorAll('.research-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs and contents
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding content
            this.classList.add('active');
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // URL parameter handling for tabs
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    if (tabParam) {
        const targetTab = document.querySelector(`.research-tab[data-tab="${tabParam}"]`);
        if (targetTab) {
            targetTab.click();
        }
    }
    
    // Auto-sync notification (simulated)
    console.log('Research Support page loaded successfully');
    
    // Add loading animation for images
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        img.addEventListener('load', function() {
            this.style.opacity = '1';
        });
        img.style.opacity = '0';
        if (img.complete) {
            img.style.opacity = '1';
        }
    });
});
</script>

<?php
include 'partials/footer.php';
?>
