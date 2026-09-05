<?php
session_start();
include 'partials/header.php';
require 'admin/config/database.php';

// Fetch approved ceremonies titles for suggestions
$query = "SELECT title FROM fante_ceremonies WHERE status = 'approved' ORDER BY title ASC";
$result = mysqli_query($connection, $query);
$titles = [];
while ($row = mysqli_fetch_assoc($result)) {
    $titles[] = $row['title'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fante Ceremonies</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .ceremonies-container {
            max-width: 1200px;
            margin: 6rem auto 0 auto;
            padding: 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border: 5rem solid transparent;
            border-radius: 5rem;
            backdrop-filter: blur(10px);
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            background: rgba(206, 67, 211, 0.1);
            border: 5rem solid transparent;
            border-radius: 5rem;
            backdrop-filter: blur(10px);
        }

        .search-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 3rem;
            margin-top: 3rem;
        }
        .search-section h1 {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }
        .search-section p {
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }

        .view-toggle {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .view-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid rgba(255,255,255,0.3);
            padding: 0.5rem 1rem;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .view-btn.active {
            background: rgba(255,255,255,0.8);
            color: #333;
            border-color: rgba(255,255,255,0.8);
        }

        .view-btn:hover {
            background: rgba(255,255,255,0.4);
            transform: scale(1.05);
        }

        .search-bar input {
            transition: box-shadow 0.3s ease;
        }

        .search-bar input:focus {
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.7);
        }

        .suggestion-item:hover {
            background: #4CAF50;
            color: white;
        }

        .search-bar {
            position: relative;
            max-width: 600px;
            margin: 0 auto;
        }

        #search-input {
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

        .suggestions {
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

        .suggestion-item {
            padding: 1rem;
            cursor: pointer;
            border-bottom: 1px solid #eee;
            transition: background 0.3s ease;
        }

        .suggestion-item:hover {
            background: #2e6397;
        }

        .result-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-top: 3rem;
        }

        .image-display, .details-display {
            background: rgba(255,255,255,0.1);
            padding: 2rem;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 5px solid rgba(255,255,255,0.2);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .image-display:hover, .details-display:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
        }

        .image-display h2, .details-display h2 {
            margin-bottom: 1rem;
            color: #fff;
        }

        .ceremony-image {
            max-width: 100%;
            max-height: 400px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            display: none;
        }

        .details-textbox {
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

        .video-section {
            grid-column: span 2;
            background: rgba(255,255,255,0.1);
            padding: 2rem;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 5px solid rgba(255,255,255,0.2);
            text-align: center;
            margin-top: 2rem;
        }

        .ceremony-video {
            max-width: 100%;
            max-height: 400px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            display: none;
        }

        .audio-controls {
            grid-column: span 2;
            margin-top: 2rem;
            text-align: center;
        }

        .audio-btn {
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

        .audio-btn:hover {
            transform: scale(1.05);
            background-color: #4CAF50;
        }

        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            display: none;
        }

        .alert.success {
            background: #4CAF50;
            color: white;
        }

        .alert.error {
            background: #f44336;
            color: white;
        }

        .related-links {
            grid-column: span 2;
            margin-top: 3rem;
            text-align: center;
        }

        .related-links h3 {
            color: white;
            margin-bottom: 1rem;
        }

        .links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .link-item {
            background: rgba(21, 160, 141, 0.55);
            padding: 1rem;
            border-radius: 10px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255,255,255,0.2);
            transition: transform 0.3s ease;
        }

        .link-item:hover {
            transform: translateY(-5px);
        }

        .link-item a {
            color: white;
            text-decoration: none;
            font-weight: 600;
        }

        .link-item a:hover {
            text-decoration: underline;
        }

        /* Dark Mode Support */
        body.dark-mode .ceremonies-container {
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
        }

        body.dark-mode .container {
            background: rgba(31, 41, 55, 0.9);
        }

        body.dark-mode .search-section h1,
        body.dark-mode .search-section p {
            color: #e5e7eb;
        }

        body.dark-mode .view-btn {
            background: rgba(31, 41, 55, 0.8);
            color: #e5e7eb;
            border-color: #4b5563;
        }

        body.dark-mode .view-btn.active {
            background: #667eea;
            color: white;
        }

        body.dark-mode #search-input {
            background: #374151;
            color: #e5e7eb;
        }

        body.dark-mode #search-input::placeholder {
            color: #9ca3af;
        }

        body.dark-mode .suggestions {
            background: #1f2937;
        }

        body.dark-mode .suggestion-item {
            color: #e5e7eb;
        }

        body.dark-mode .suggestion-item:hover {
            background: #374151;
        }

        body.dark-mode .image-display,
        body.dark-mode .details-display {
            background: rgba(31, 41, 55, 0.8);
        }

        body.dark-mode .image-display h2,
        body.dark-mode .details-display h2 {
            color: #e5e7eb;
        }

        body.dark-mode .details-textbox {
            background: #374151;
            color: #e5e7eb;
        }

        body.dark-mode .video-section {
            background: rgba(31, 41, 55, 0.8);
        }

        body.dark-mode .video-section h2 {
            color: #e5e7eb;
        }

        body.dark-mode .audio-btn {
            background: #667eea;
        }

        body.dark-mode .audio-btn:hover {
            background: #7c8ff5;
        }

        body.dark-mode .alert.success {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
        }

        body.dark-mode .alert.error {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
        }

        body.dark-mode .link-item {
            background: rgba(31, 41, 55, 0.8);
            border-color: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
    <div class="ceremonies-container">
        <div class="alert success" id="success-alert">Ceremony found!</div>
        <div class="alert error" id="error-alert">Ceremony not found.</div>

        <div class="search-section">
            <h1>Fante Ceremonies</h1>
            <p>Search for Fante ceremonies and discover the rich cultural heritage</p>
            <div class="view-toggle">
                <button id="single-view-btn" class="view-btn active">Single View</button>
                <button id="grid-view-btn" class="view-btn">Grid View</button>
            </div>
            <div class="search-bar">
                <input type="text" id="search-input" placeholder="Search for a ceremony title..." autocomplete="off">
                <div class="suggestions" id="suggestions"></div>
            </div>
        </div>

        <div class="result-section">
            <div class="image-display">
                <h2>Ceremony Image</h2>
                <img id="ceremony-image" class="ceremony-image" alt="Ceremony Image">
            </div>

            <div class="details-display">
                <h2>Ceremony Details</h2>
                <textarea class="details-textbox" id="details-display" readonly></textarea>
            </div>

            <div class="video-section">
                <h2>Video</h2>
                <video id="ceremony-video" class="ceremony-video" controls></video>
            </div>
        </div>

        <div class="audio-controls">
            <button class="audio-btn" id="play-btn">🔊 Play Details</button>
            <button class="audio-btn" id="pause-btn" style="display: none;">⏸️ Pause</button>
            <button class="audio-btn" id="stop-btn" style="display: none;">⏹️ Stop</button>
        </div>

        <div class="related-links">
            <h3>Related Fante Ceremonies Resources</h3>
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
                    <a href="https://fanteceremonies.com/" target="_blank">Fante Ceremonies Archive</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const titles = <?php echo json_encode($titles); ?>;
        const searchInput = document.getElementById('search-input');
        const suggestionsDiv = document.getElementById('suggestions');
        const ceremonyImage = document.getElementById('ceremony-image');
        const detailsDisplay = document.getElementById('details-display');
        const ceremonyVideo = document.getElementById('ceremony-video');
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
            fetchCeremonyData(title);
        }

        // Fetch ceremony data via AJAX
        function fetchCeremonyData(title) {
            fetch('admin/get-ceremony-entry.php?word=' + encodeURIComponent(title))
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.entry) {
                        detailsDisplay.value = data.entry.description;
                        if (data.entry.image) {
                            ceremonyImage.src = 'images/' + data.entry.image;
                            ceremonyImage.style.display = 'block';
                        } else {
                            ceremonyImage.style.display = 'none';
                        }
                        if (data.entry.video) {
                            ceremonyVideo.src = 'images/' + data.entry.video;
                            ceremonyVideo.style.display = 'block';
                        } else {
                            ceremonyVideo.style.display = 'none';
                        }
                        successAlert.style.display = 'block';
                        errorAlert.style.display = 'none';
                        setTimeout(() => successAlert.style.display = 'none', 3000);
                    } else {
                        detailsDisplay.value = '';
                        ceremonyImage.style.display = 'none';
                        ceremonyVideo.style.display = 'none';
                        errorAlert.style.display = 'block';
                        successAlert.style.display = 'none';
                        setTimeout(() => errorAlert.style.display = 'none', 3000);
                    }
                })
                .catch(error => {
                    console.error('Error fetching ceremony data:', error);
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
                fetchCeremonyData(this.value);
            }
        });
    </script>

    <?php include 'partials/footer.php'; ?>
</body>
</html>
