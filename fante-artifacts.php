<?php
session_start();
include 'partials/header.php';
require 'admin/config/database.php';

// Fetch approved artifacts titles for suggestions
$query = "SELECT title FROM fante_artifacts WHERE status = 'approved' ORDER BY title ASC";
$result = mysqli_query($connection, $query);
$titles = [];
while ($row = mysqli_fetch_assoc($result)) {
    $titles[] = $row['title'];
}

// Fetch categories for filter
$category_query = "SELECT DISTINCT category FROM fante_artifacts WHERE status = 'approved' AND category IS NOT NULL ORDER BY category ASC";
$category_result = mysqli_query($connection, $category_query);
$categories = [];
while ($row = mysqli_fetch_assoc($category_result)) {
    $categories[] = $row['category'];
}

// Get filter parameters
$view = isset($_GET['view']) ? $_GET['view'] : 'single';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$rating_filter = isset($_GET['rating']) ? (float)$_GET['rating'] : 0;
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch all approved artifacts for grid view
$artifacts = [];
if ($view === 'grid') {
    $grid_query = "SELECT * FROM fante_artifacts WHERE status = 'approved'";
    $params = [];
    $types = '';

    if ($category_filter) {
        $grid_query .= " AND category = ?";
        $params[] = $category_filter;
        $types .= 's';
    }

    if ($rating_filter > 0) {
        $grid_query .= " AND rating >= ?";
        $params[] = $rating_filter;
        $types .= 'd';
    }

    if ($search) {
        $grid_query .= " AND (title LIKE ? OR description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $types .= 'ss';
    }

    $grid_query .= " ORDER BY created_at DESC";

    $stmt = mysqli_prepare($connection, $grid_query);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $artifacts[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fante Artifacts</title>

    <link rel="stylesheet" href="<?= ROOT_URL ?>css/style.css">
    <style>
        .artifacts-page {
            margin-top: 8rem;
        }
        .artifacts-page .modern-hero.search-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 3rem;
            margin-top: 3rem;
        }
        .artifacts-page .modern-hero.search-section h1 {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }
        .artifacts-page .modern-hero.search-section p {
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }

        .artifacts-page .view-toggle {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .artifacts-page .view-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid rgba(255,255,255,0.3);
            padding: 0.5rem 1rem;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .artifacts-page .view-btn.active {
            background: rgba(255,255,255,0.8);
            color: #333;
            border-color: rgba(255,255,255,0.8);
        }

        .artifacts-page .view-btn:hover {
            background: rgba(255,255,255,0.4);
            transform: scale(1.05);
        }

        .artifacts-page .search-bar input {
            transition: box-shadow 0.3s ease;
        }

        .artifacts-page .search-bar input:focus {
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.7);
        }

        .artifacts-page .suggestion-item:hover {
            background: #4CAF50;
            color: white;
        }

        .artifacts-page .search-bar {
            position: relative;
            max-width: 600px;
            margin: 0 auto;
        }

        .artifacts-page #search-input {
            width: 100%;
            padding: 1rem 2rem;
            font-size: 1.2rem;
            border: none;
            border-radius: 50px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            outline: none;
            background: rgba(255,255,255,0.9);
            color: #333;
        }

        .artifacts-page .suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }

        .artifacts-page .suggestion-item {
            padding: 1rem;
            cursor: pointer;
            border-bottom: 1px solid #eee;
            transition: background 0.3s ease;
        }

        .artifacts-page .suggestion-item:hover {
            background: #2e6397;
        }

        .artifacts-page .result-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-top: 3rem;
        }

        .artifacts-page .image-display, .artifacts-page .details-display {
            background: rgba(255,255,255,0.1);
            padding: 2rem;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 5px solid rgba(255,255,255,0.2);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .artifacts-page .image-display:hover, .artifacts-page .details-display:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
        }

        .artifacts-page .image-display h2, .artifacts-page .details-display h2 {
            margin-bottom: 1rem;
            color: #fff;
        }

        .artifacts-page .artifact-image {
            max-width: 100%;
            max-height: 400px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            display: none;
        }

        .artifacts-page .details-textbox {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 10px;
            background: rgba(255,255,255,0.9);
            color: #333;
            font-size: 1.1rem;
            resize: vertical;
            min-height: 200px;
        }

        .artifacts-page .video-section {
            grid-column: span 2;
            background: rgba(255,255,255,0.1);
            padding: 2rem;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 5px solid rgba(255,255,255,0.2);
            text-align: center;
            margin-top: 2rem;
        }

        .artifacts-page .artifact-video {
            max-width: 100%;
            max-height: 400px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            display: none;
        }

        .artifacts-page .audio-controls {
            grid-column: span 2;
            margin-top: 2rem;
            text-align: center;
        }

        .artifacts-page .audio-btn {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: transform 0.3s ease;
            margin: 0 0.5rem;
        }

        .artifacts-page .audio-btn:hover {
            transform: scale(1.05);
            background-color: #4CAF50;
        }

        .artifacts-page .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            display: none;
        }

        .artifacts-page .alert.success {
            background: #4CAF50;
            color: white;
        }

        .artifacts-page .alert.error {
            background: #f44336;
            color: white;
        }

        .artifacts-page .related-links {
            grid-column: span 2;
            margin-top: 3rem;
            text-align: center;
        }

        .artifacts-page .related-links h3 {
            color: white;
            margin-bottom: 1rem;
        }

        .artifacts-page .links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .artifacts-page .link-item {
            background: rgba(21, 160, 141, 0.55);
            padding: 1rem;
            border-radius: 10px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255,255,255,0.2);
            transition: transform 0.3s ease;
        }

        .artifacts-page .link-item:hover {
            transform: translateY(-5px);
        }

        .artifacts-page .link-item a {
            color: white;
            text-decoration: none;
            font-weight: 600;
        }

        .artifacts-page .link-item a:hover {
            text-decoration: underline;
        }

        /* Dark Mode Support */
        body.dark-mode .artifacts-page .modern-hero.search-section h1,
        body.dark-mode .artifacts-page .modern-hero.search-section p {
            color: #e5e7eb;
        }

        body.dark-mode .artifacts-page .view-btn {
            background: rgba(31, 41, 55, 0.8);
            color: #e5e7eb;
            border-color: #4b5563;
        }

        body.dark-mode .artifacts-page .view-btn.active {
            background: #667eea;
            color: white;
        }

        body.dark-mode .artifacts-page #search-input {
            background: #374151;
            color: #e5e7eb;
        }

        body.dark-mode .artifacts-page #search-input::placeholder {
            color: #9ca3af;
        }

        body.dark-mode .artifacts-page .suggestions {
            background: #1f2937;
        }

        body.dark-mode .artifacts-page .suggestion-item {
            color: #e5e7eb;
        }

        body.dark-mode .artifacts-page .suggestion-item:hover {
            background: #374151;
        }

        body.dark-mode .artifacts-page .image-display,
        body.dark-mode .artifacts-page .details-display {
            background: rgba(31, 41, 55, 0.8);
        }

        body.dark-mode .artifacts-page .image-display h2,
        body.dark-mode .artifacts-page .details-display h2 {
            color: #e5e7eb;
        }

        body.dark-mode .artifacts-page .details-textbox {
            background: #374151;
            color: #e5e7eb;
        }

        body.dark-mode .artifacts-page .video-section {
            background: rgba(31, 41, 55, 0.8);
        }

        body.dark-mode .artifacts-page .video-section h2 {
            color: #e5e7eb;
        }

        body.dark-mode .artifacts-page .audio-btn {
            background: #667eea;
        }

        body.dark-mode .artifacts-page .audio-btn:hover {
            background: #7c8ff5;
        }

        body.dark-mode .artifacts-page .alert.success {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
        }

        body.dark-mode .artifacts-page .alert.error {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
        }

        body.dark-mode .artifacts-page .link-item {
            background: rgba(31, 41, 55, 0.8);
            border-color: rgba(255, 255, 255, 0.1);
        }
    </style>

</head>
<body>
<section class="section__extra-margin artifacts-page">
    <div class="container">
        <div class="modern-hero search-section">
        <div class="alert success" id="success-alert">Artifact found!</div>
        <div class="alert error" id="error-alert">Artifact not found.</div>

        <div class="search-section">
            <h1>Fante Artifacts</h1>
            <p>Search for Fante artifacts and discover the rich cultural heritage</p>
            <div class="view-toggle">
                <button id="single-view-btn" class="view-btn active">Single View</button>
                <button id="grid-view-btn" class="view-btn">Grid View</button>
            </div>
            <div class="search-bar">
                <input type="text" id="search-input" placeholder="Search for an artifact title..." autocomplete="off">
                <div class="suggestions" id="suggestions"></div>
            </div>
        </div>

        <div class="result-section">
            <div class="image-display">
                <h2>Artifact Image</h2>
                <img id="artifact-image" class="artifact-image" alt="Artifact Image">
            </div>

            <div class="details-display">
                <h2>Artifact Details</h2>
                <textarea class="details-textbox" id="details-display" readonly></textarea>
            </div>

            <div class="video-section">
                <h2>Video</h2>
                <video id="artifact-video" class="artifact-video" controls></video>
            </div>
        </div>

        <div class="audio-controls">
            <button class="audio-btn" id="play-btn">🔊 Play Details</button>
            <button class="audio-btn" id="pause-btn" style="display: none;">⏸️ Pause</button>
            <button class="audio-btn" id="stop-btn" style="display: none;">⏹️ Stop</button>
        </div>

        <div class="related-links">
            <h3>Related Fante Artifacts Resources</h3>
            <div class="links-grid">
                <div class="link-item">
                    <a href="https://en.wikipedia.org/wiki/Fante_people" target="_blank">Fante People - Wikipedia</a>
                </div>
                <div class="link-item">
                    <a href="https://www.britannica.com/topic/Fante" target="_blank">Fante - Britannica</a>
                </div>
                <div class="link-item">
                    <a href="https://fanteakrafena.org/" target="_blank">Fante Akrafena Organization</a>
                </div>
                <div class="link-item">
                    <a href="https://www.ghanaculture.gov.gh/" target="_blank">Ghana Culture Ministry</a>
                </div>
                <div class="link-item">
                    <a href="https://www.africanhistory.com/" target="_blank">African History Resources</a>
                </div>
                <div class="link-item">
                    <a href="https://fanteartifacts.com/" target="_blank">Fante Artifacts Archive</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const titles = <?php echo json_encode($titles); ?>;
        const searchInput = document.getElementById('search-input');
        const suggestionsDiv = document.getElementById('suggestions');
        const artifactImage = document.getElementById('artifact-image');
        const detailsDisplay = document.getElementById('details-display');
        const artifactVideo = document.getElementById('artifact-video');
        const successAlert = document.getElementById('success-alert');
        const errorAlert = document.getElementById('error-alert');
        const playBtn = document.getElementById('play-btn');
        const pauseBtn = document.getElementById('pause-btn');
        const stopBtn = document.getElementById('stop-btn');

        let speechSynthesis = window.speechSynthesis;
        let currentUtterance = null;

        // Search functionality
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            suggestionsDiv.innerHTML = '';
            if (query.length > 0) {
                const filteredTitles = titles.filter(title => title.toLowerCase().startsWith(query)).slice(0, 10);
                if (filteredTitles.length > 0) {
                    suggestionsDiv.style.display = 'block';
                    filteredTitles.forEach(title => {
                        const div = document.createElement('div');
                        div.className = 'suggestion-item';
                        div.textContent = title;
                        div.addEventListener('click', () => selectTitle(title));
                        suggestionsDiv.appendChild(div);
                    });
                } else {
                    suggestionsDiv.style.display = 'none';
                }
            } else {
                suggestionsDiv.style.display = 'none';
            }
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
                suggestionsDiv.style.display = 'none';
            }
        });

        // Select title from suggestions
        function selectTitle(title) {
            searchInput.value = title;
            suggestionsDiv.style.display = 'none';
            fetchArtifactData(title);
        }

        // Fetch artifact data via AJAX
        function fetchArtifactData(title) {
            fetch('admin/get-artifact-entry.php?word=' + encodeURIComponent(title))
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.entry) {
                        detailsDisplay.value = data.entry.description;
                        if (data.entry.image) {
                            artifactImage.src = 'images/' + data.entry.image;
                            artifactImage.style.display = 'block';
                        } else {
                            artifactImage.style.display = 'none';
                        }
                        if (data.entry.video) {
                            artifactVideo.src = 'images/' + data.entry.video;
                            artifactVideo.style.display = 'block';
                        } else {
                            artifactVideo.style.display = 'none';
                        }
                        successAlert.style.display = 'block';
                        errorAlert.style.display = 'none';
                        setTimeout(() => successAlert.style.display = 'none', 3000);
                    } else {
                        detailsDisplay.value = '';
                        artifactImage.style.display = 'none';
                        artifactVideo.style.display = 'none';
                        errorAlert.style.display = 'block';
                        successAlert.style.display = 'none';
                        setTimeout(() => errorAlert.style.display = 'none', 3000);
                    }
                })
                .catch(error => {
                    console.error('Error fetching artifact data:', error);
                    errorAlert.style.display = 'block';
                    successAlert.style.display = 'none';
                    setTimeout(() => errorAlert.style.display = 'none', 3000);
                });
        }

        // Audio controls
        playBtn.addEventListener('click', function() {
            if (detailsDisplay.value) {
                if (speechSynthesis.speaking) {
                    speechSynthesis.cancel();
                }
                currentUtterance = new SpeechSynthesisUtterance(detailsDisplay.value);
                currentUtterance.lang = 'en-US'; // Adjust language if needed
                speechSynthesis.speak(currentUtterance);
                playBtn.style.display = 'none';
                pauseBtn.style.display = 'inline-block';
                stopBtn.style.display = 'inline-block';
            }
        });

        pauseBtn.addEventListener('click', function() {
            if (speechSynthesis.speaking) {
                speechSynthesis.pause();
                pauseBtn.textContent = '▶️ Resume';
            } else if (speechSynthesis.paused) {
                speechSynthesis.resume();
                pauseBtn.textContent = '⏸️ Pause';
            }
        });

        stopBtn.addEventListener('click', function() {
            speechSynthesis.cancel();
            resetAudioButtons();
        });

        speechSynthesis.addEventListener('end', resetAudioButtons);

        function resetAudioButtons() {
            playBtn.style.display = 'inline-block';
            pauseBtn.style.display = 'none';
            stopBtn.style.display = 'none';
            pauseBtn.textContent = '⏸️ Pause';
        }

        // Allow search on Enter key
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                fetchArtifactData(this.value);
            }
        });
    </script>

        </div>
    </div>
</section>

    <?php include 'partials/footer.php'; ?>
</body>
</html>
