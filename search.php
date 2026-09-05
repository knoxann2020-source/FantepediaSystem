<?php
session_start();
require 'config/constants.php';
require 'config/database.php';

// Sanitize the search query to prevent SQL injection and unwanted queries
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

// Additional security: remove any SQL keywords from the search term to prevent injection
$forbidden_patterns = '/\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|UNION|WHERE|FROM|JOIN)\b/i';
$query = preg_replace($forbidden_patterns, '', $query);
$query = trim($query);

include 'partials/header.php';

// Search logic
$results = [];
if (!empty($query)) {
    // Search in posts
    $post_query = "SELECT p.id, p.title, p.body, u.username, p.created_at, 'post' as type
                   FROM posts p
                   JOIN users u ON p.user_id = u.id
                   WHERE p.title LIKE ? OR p.body LIKE ? OR u.username LIKE ?";
    $stmt = mysqli_prepare($connection, $post_query);
    $search_term = '%' . $query . '%';
    mysqli_stmt_bind_param($stmt, 'sss', $search_term, $search_term, $search_term);
    mysqli_stmt_execute($stmt);
    $post_results = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($post_results)) {
        $results[] = $row;
    }

    // Search in users
    $user_query = "SELECT id, username, email, 'user' as type
                   FROM users
                   WHERE username LIKE ? OR email LIKE ?";
    $stmt = mysqli_prepare($connection, $user_query);
    mysqli_stmt_bind_param($stmt, 'ss', $search_term, $search_term);
    mysqli_stmt_execute($stmt);
    $user_results = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($user_results)) {
        $results[] = $row;
    }

// Search in categories
    $category_query = "SELECT id, title, description, 'category' as type
                       FROM categories
                       WHERE title LIKE ? OR description LIKE ?";
    $stmt = mysqli_prepare($connection, $category_query);
    mysqli_stmt_bind_param($stmt, 'ss', $search_term, $search_term);
    mysqli_stmt_execute($stmt);
    $category_results = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($category_results)) {
        $results[] = $row;
    }

    // Search in Music & Dance
    $music_query = "SELECT id, title, description, category, image, audio, video, created_at, 'music-dance' as type
                    FROM fante_music_dance 
                    WHERE status = 'approved' AND (title LIKE ? OR description LIKE ?)";
    $stmt = mysqli_prepare($connection, $music_query);
    mysqli_stmt_bind_param($stmt, 'ss', $search_term, $search_term);
    mysqli_stmt_execute($stmt);
    $music_results = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($music_results)) {
        $results[] = $row;
    }

    // Search in files (images directory)
    $files = glob('images/*');

    foreach ($files as $file) {
        if (stripos(basename($file), $query) !== false) {
            $results[] = [
                'id' => basename($file),
                'title' => basename($file),
                'type' => 'file',
                'path' => $file
            ];
        }
    }
}
?>

<section class="search section section__extra-margin">
    <div class="container">
        <h2>Search Results for "<?php echo htmlspecialchars($query); ?>"</h2>

        <!-- AI Search Suggestions Container -->
        <div id="ai-suggestions"></div>

        <?php if (empty($query)): ?>
            <p>Please enter a search term.</p>
        <?php elseif (empty($results)): ?>
            <p>No results found for "<?php echo htmlspecialchars($query); ?>".</p>
        <?php else: ?>
            <div class="search-results">
                <?php foreach ($results as $result): ?>
                    <div class="search-result-item">
                        <?php if ($result['type'] === 'post'): ?>
                            <h3><a href="post.php?id=<?php echo $result['id']; ?>"><?php echo htmlspecialchars($result['title']); ?></a></h3>
                            <p>By <?php echo htmlspecialchars($result['username']); ?> on <?php echo date('M d, Y', strtotime($result['created_at'])); ?></p>
                            <p><?php echo htmlspecialchars(substr($result['body'], 0, 200)) . '...'; ?></p>
                            <span class="result-type post">Post</span>
                        <?php elseif ($result['type'] === 'user'): ?>
                            <h3><?php echo htmlspecialchars($result['username']); ?></h3>
                            <p><?php echo htmlspecialchars($result['email']); ?></p>
                            <span class="result-type user">User</span>
                        <?php elseif ($result['type'] === 'category'): ?>
                            <h3><?php echo htmlspecialchars($result['title']); ?></h3>
                            <p><?php echo htmlspecialchars($result['description']); ?></p>
                            <span class="result-type category">Category</span>
                        <?php elseif ($result['type'] === 'music-dance'): ?>
                            <h3><a href="<?= ROOT_URL ?>music-dance.php?search=<?= urlencode($query) ?>&category=<?= urlencode($result['category']) ?>"><?= htmlspecialchars($result['title']) ?></a></h3>
                            <p><i class="fas <?= $result['category']=='Music' ? 'fa-music' : 'fa-dancing' ?>"></i> <?= htmlspecialchars($result['category']) ?> · <?= date('M d, Y', strtotime($result['created_at'])) ?></p>
                            <p><?= htmlspecialchars(substr($result['description'], 0, 150)) ?>... <a href="<?= ROOT_URL ?>music-dance.php?search=<?= urlencode($query) ?>">View All Matches</a></p>
                            <?php if ($result['audio'] || $result['video']): ?>
                                <span class="result-type music-dance" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white;">🎵 Has <?= $result['audio'] ? 'Audio' : '' ?> <?= $result['video'] ? 'Video' : '' ?> Controls</span>
                            <?php else: ?>
                                <span class="result-type music-dance">Music & Dance</span>
                            <?php endif; ?>
                        <?php elseif ($result['type'] === 'file'): ?>
                            <h3><?php echo htmlspecialchars($result['title']); ?></h3>
                            <p>File: <?php echo htmlspecialchars($result['path']); ?></p>
                            <span class="result-type file">File</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<script src="js/ai-assistant.js"></script>
<script>
// Add some JavaScript for enhanced search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="query"]');
    const searchForm = document.querySelector('.search__form');
    
    if (searchInput) {
        // Add AI search enhancement
        let enhanceTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(enhanceTimeout);
            const query = this.value.trim();
            
            if (query.length > 3) {
                // Debounce AI enhancement
                enhanceTimeout = setTimeout(function() {
                    enhanceSearch(query);
                }, 500);
            }
        });
    }
    
    // AI Search Enhancement Function
    function enhanceSearch(query) {
        const suggestionsContainer = document.getElementById('ai-suggestions');
        if (!suggestionsContainer) return;
        
        // Show loading state
        suggestionsContainer.innerHTML = '<p class="enhancing">🤖 AI is enhancing your search...</p>';
        suggestionsContainer.style.display = 'block';
        
        // Call AI API
        AIAssistant.enhanceSearch(query, function(response) {
            if (response.success) {
                let suggestionsHTML = '<div class="ai-search-suggestions">';
                suggestionsHTML += '<p><strong>💡 AI Suggestions:</strong></p>';
                suggestionsHTML += '<ul>';
                
                response.suggestions.forEach(function(suggestion) {
                    suggestion = suggestion.trim();
                    if (suggestion) {
                        suggestionsHTML += '<li><a href="search.php?query=' + encodeURIComponent(suggestion) + '">' + suggestion + '</a></li>';
                    }
                });
                
                suggestionsHTML += '</ul>';
                suggestionsHTML += '</div>';
                
                suggestionsContainer.innerHTML = suggestionsHTML;
            } else {
                suggestionsContainer.innerHTML = '';
            }
        }).catch(function(error) {
            suggestionsContainer.innerHTML = '';
        });
    }
});
</script>

<style>
.ai-search-suggestions {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin: 20px 0;
    border-left: 4px solid #667eea;
}

.ai-search-suggestions p {
    margin: 0 0 10px 0;
    color: #333;
    font-weight: bold;
}

.ai-search-suggestions ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.ai-search-suggestions li {
    padding: 5px 0;
}

.ai-search-suggestions a {
    color: #667eea;
    text-decoration: none;
    transition: color 0.3s;
}

.ai-search-suggestions a:hover {
    color: #5568d3;
    text-decoration: underline;
}

.enhancing {
    color: #666;
    font-style: italic;
}

#ai-suggestions {
    display: none;
}
</style>

<?php
include 'partials/footer.php';
?>
