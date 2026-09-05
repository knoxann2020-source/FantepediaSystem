<?php
session_start();
include 'partials/header.php';
require 'admin/config/database.php';

// Check/enhance table
$table_check_query = "SHOW TABLES LIKE 'fante_history'";
$table_check_result = mysqli_query($connection, $table_check_query);
if (mysqli_num_rows($table_check_result) == 0) {
    $create_table_query = "CREATE TABLE fante_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        details TEXT NOT NULL,
        origin TEXT,
        visuals VARCHAR(255),
        status ENUM('pending', 'approved', 'rejected', 'draft') DEFAULT 'pending',
        user_id INT,
        admin_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    mysqli_query($connection, $create_table_query);
} else {
    // Add missing columns
    $add_origin = mysqli_query($connection, "SHOW COLUMNS FROM fante_history LIKE 'origin'");
    if (mysqli_num_rows($add_origin) == 0) {
        mysqli_query($connection, "ALTER TABLE fante_history ADD origin TEXT");
    }
    $add_visuals = mysqli_query($connection, "SHOW COLUMNS FROM fante_history LIKE 'visuals'");
    if (mysqli_num_rows($add_visuals) == 0) {
        mysqli_query($connection, "ALTER TABLE fante_history ADD visuals VARCHAR(255)");
    }
}

// Fetch approved titles for suggestions
$query = "SELECT title FROM fante_history WHERE status = 'approved' ORDER BY title ASC";
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
    <title>Fante History Archive</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .history-hero {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/pusuban.webp');
            background-size: cover; background-position: center; padding: 6rem 2rem; border-radius: 30px; margin: 4rem 2rem 3rem; color: white; text-align: center; box-shadow: 0 20px 60px rgba(0,0,0,0.4);
        }
        .history-container {
            max-width: 1400px; margin: 0 auto 4rem; padding: 3rem; background: rgba(147, 167, 128, 0.95); border-radius: 5rem; border: 5rem; box-shadow: 0 20px 60px rgba(0,0,0,0.2); backdrop-filter: blur(20px);
        }
        .search-section { text-align: center; margin-bottom: 4rem; }
        .search-section h1 { font-size: 3.5rem; background: linear-gradient(135deg, #667eea, #764ba2); background-clip: text; -webkit-background-clip: text; color: transparent; -webkit-text-fill-color: transparent; margin-bottom: 1rem; }
        .search-section p { font-size: 1.3rem; color: #666; max-width: 600px; margin: 0 auto 3rem; }
        .search-bar { position: relative; max-width: 700px; margin: 0 auto; }
        #search-input { width: 100%; padding: 1.5rem 2.5rem; font-size: 1.3rem; border: none; border-radius: 50px; box-shadow: 0 15px 40px rgba(0,0,0,0.15); outline: none; background: white; color: #333; transition: all 0.4s; }
        #search-input:focus { box-shadow: 0 20px 50px rgba(102,126,234,0.3); transform: scale(1.02); }
        .suggestions { position: absolute; top: 110%; left: 0; right: 0; background: white; border-radius: 20px; box-shadow: 0 20px 50px rgba(0,0,0,0.2); max-height: 400px; overflow-y: auto; z-index: 1000; display: none; }
        .suggestion-item { padding: 1.5rem 2rem; cursor: pointer; border-bottom: 1px solid #eee; transition: all 0.3s; font-size: 1.1rem; }
        .suggestion-item:hover { background: linear-gradient(135deg, #667eea, #764ba2); color: white; transform: translateX(10px); }
        .result-section { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 3rem; margin-top: 4rem; }
        .display-card { background: linear-gradient(135deg, rgba(102,126,234,0.1), rgba(118,75,162,0.1)); padding: 3rem; border-radius: 25px; backdrop-filter: blur(20px); border: 2px solid rgba(255,255,255,0.3); transition: all 0.4s; }
        .display-card:hover { transform: translateY(-10px); box-shadow: 0 25px 70px rgba(0,0,0,0.2); }
        .display-card h2 { color: #333; margin-bottom: 1.5rem; font-size: 1.8rem; display: flex; align-items: center; gap: 1rem; }
        .title-textbox, .details-textbox, .origin-textbox { width: 100%; padding: 1.5rem; border: none; border-radius: 20px; background: rgba(255,255,255,0.9); color: #333; font-size: 1.2rem; resize: vertical; transition: all 0.3s; box-shadow: inset 0 5px 15px rgba(0,0,0,0.05); }
        .details-textbox.zoomable { transition: transform 0.3s ease; cursor: zoom-in; }
        .visuals-section { background: rgba(255,255,255,0.9); padding: 2.5rem; border-radius: 20px; text-align: center; margin-top: 2rem; box-shadow: inset 0 5px 15px rgba(0,0,0,0.05); }
        .history-media { max-width: 100%; max-height: 450px; border-radius: 20px; box-shadow: 0 15px 40px rgba(0,0,0,0.2); display: none; }
        .controls-section { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 2.5rem; }
        .audio-btn, .zoom-btn { background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; padding: 1.2rem 2rem; border-radius: 50px; font-size: 1.1rem; cursor: pointer; transition: all 0.3s; font-weight: 600; box-shadow: 0 10px 30px rgba(102,126,234,0.3); }
        .audio-btn:hover, .zoom-btn:hover { transform: scale(1.08); box-shadow: 0 15px 40px rgba(102,126,234,0.4); }
        .alert { padding: 1.5rem 2rem; border-radius: 20px; margin-bottom: 3rem; display: none; font-weight: 600; text-align: center; }
        .alert.success { background: linear-gradient(135deg, #4CAF50, #45a049); color: white; }
        .alert.error { background: linear-gradient(135deg, #f44336, #da190b); color: white; }
        .related-links { margin-top: 6rem; }
        .links-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; }
        .link-item { background: linear-gradient(135deg, rgba(102,126,234,0.2), rgba(118,75,162,0.2)); padding: 2rem; border-radius: 20px; backdrop-filter: blur(10px); border: 2px solid rgba(255,255,255,0.3); transition: all 0.4s; text-align: center; }
        .link-item:hover { transform: translateY(-15px); box-shadow: 0 25px 60px rgba(0,0,0,0.3); }
        .link-item a { color: #333; text-decoration: none; font-weight: 700; font-size: 1.2rem; }
        .link-item i { font-size: 3rem; margin-bottom: 1rem; display: block; }
        @media (max-width: 768px) { .result-section { grid-template-columns: 1fr; gap: 2rem; } .history-hero { margin: 2rem 1rem 2rem; padding: 4rem 1.5rem; } .search-section h1 { font-size: 2.5rem; } }
        @media (max-width: 480px) { .controls-section { flex-direction: column; align-items: center; } }
    </style>
</head>
<body>
    <div class="history-hero">
        <h1><i class="fas fa-scroll"></i> Fante History Archive</h1>
        <p>Discover the rich heritage and historical narratives of the Fante people through authenticated entries and multimedia</p>
    </div>
    
    <div class="history-container">
        <div class="alert success" id="success-alert">Entry loaded successfully!</div>
        <div class="alert error" id="error-alert">No matching history found. Try another title.</div>

        <div class="search-section">
            <div class="search-bar">
                <input type="text" id="search-input" placeholder="Search Fante history by title..." autocomplete="off">
                <div class="suggestions" id="suggestions"></div>
            </div>
        </div>

        <div class="result-section">
            <div class="display-card title-display">
                <h2><i class="fas fa-heading"></i> Title</h2>
                <input type="text" class="title-textbox" id="title-display" readonly>
            </div>

            <div class="display-card origin-display">
                <h2><i class="fas fa-globe"></i> Origin</h2>
                <textarea class="origin-textbox" id="origin-display" readonly rows="4" placeholder="Origin information will appear here..."></textarea>
            </div>

            <div class="display-card details-display">
                <h2><i class="fas fa-align-left"></i> History Details</h2>
                <textarea class="details-textbox zoomable" id="details-display" readonly rows="10" placeholder="Detailed history will appear here..."></textarea>
            </div>

            <div class="display-card visuals-section">
                <h2><i class="fas fa-play-circle"></i> Multimedia</h2>
                <div id="history-media" class="history-media"></div>
            </div>
        </div>

        <div class="controls-section">
            <button class="audio-btn" id="play-btn">🔊 Read Aloud</button>
            <button class="audio-btn" id="pause-btn" style="display:none;">⏸️ Pause</button>
            <button class="audio-btn" id="stop-btn" style="display:none;">⏹️ Stop</button>
            <button class="zoom-btn" id="zoomIn">🔍 Zoom In</button>
            <button class="zoom-btn" id="zoomOut">🔍 Zoom Out</button>
        </div>

        <div class="related-links">
            <h3>Explore More Fante Resources</h3>
            <div class="links-grid">
                <div class="link-item">
                    <i class="fab fa-wikipedia-w"></i>
                    <a href="https://en.wikipedia.org/wiki/Fante_people" target="_blank">Fante People on Wikipedia</a>
                </div>
                <div class="link-item">
                    <i class="fas fa-book"></i>
                    <a href="https://www.britannica.com/topic/Fante" target="_blank">Fante - Britannica</a>
                </div>
                <div class="link-item">
                    <i class="fas fa-globe"></i>
                    <a href="https://fanteakrafena.org/" target="_blank">Fante Akrafena</a>
                </div>
                <div class="link-item">
                    <i class="fas fa-landmark"></i>
                    <a href="https://www.ghanaculture.gov.gh/" target="_blank">Ghana Culture Ministry</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const titles = <?= json_encode($titles) ?>;
        const searchInput = document.getElementById('search-input');
        const suggestionsDiv = document.getElementById('suggestions');
        const titleDisplay = document.getElementById('title-display');
        const originDisplay = document.getElementById('origin-display');
        const detailsDisplay = document.getElementById('details-display');
        const mediaContainer = document.getElementById('history-media');
        const successAlert = document.getElementById('success-alert');
        const errorAlert = document.getElementById('error-alert');
        const playBtn = document.getElementById('play-btn');
        const pauseBtn = document.getElementById('pause-btn');
        const stopBtn = document.getElementById('stop-btn');
        const zoomInBtn = document.getElementById('zoomIn');
        const zoomOutBtn = document.getElementById('zoomOut');

        let synth = window.speechSynthesis;
        let utterance = null;
        let zoomLevel = 1;

        // Search and suggestions
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            suggestionsDiv.innerHTML = '';
            if (query.length > 0) {
                const filtered = titles.filter(title => title.toLowerCase().includes(query)).slice(0, 8);
                if (filtered.length) {
                    suggestionsDiv.style.display = 'block';
                    filtered.forEach(title => {
                        const div = document.createElement('div');
                        div.className = 'suggestion-item';
                        div.textContent = title;
                        div.onclick = () => selectTitle(title);
                        suggestionsDiv.appendChild(div);
                    });
                } else {
                    suggestionsDiv.style.display = 'none';
                }
            } else {
                suggestionsDiv.style.display = 'none';
            }
        });

        document.addEventListener('click', e => {
            if (!searchInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
                suggestionsDiv.style.display = 'none';
            }
        });

        function selectTitle(title) {
            searchInput.value = title;
            suggestionsDiv.style.display = 'none';
            fetchHistoryData(title);
        }

        // AJAX fetch
        async function fetchHistoryData(title) {
            try {
                const response = await fetch(`admin/get-history-entry.php?title=${encodeURIComponent(title)}`);
                const data = await response.json();
                if (data.success && data.entry) {
                    titleDisplay.value = data.entry.title;
                    originDisplay.value = data.entry.origin || 'Not specified';
                    detailsDisplay.value = data.entry.details;
                    showMedia(data.entry.visuals || data.entry.video);
                    showAlert(successAlert);
                    startAutoRead();
                } else {
                    clearDisplays();
                    showAlert(errorAlert);
                }
            } catch (err) {
                console.error('Fetch error:', err);
                clearDisplays();
                showAlert(errorAlert);
            }
        }

        function showMedia(visuals) {
            mediaContainer.innerHTML = '';
            if (!visuals) return mediaContainer.innerHTML = '<p style="color:#666; font-style:italic;">No multimedia available</p>';
            
            const ext = visuals.split('.').pop().toLowerCase();
            if (['jpg','jpeg','png','gif','webp'].includes(ext)) {
                mediaContainer.innerHTML = `<img src="images/history/${visuals}" alt="History Visual" class="history-media">`;
            } else if (['mp4','webm','ogg'].includes(ext)) {
                mediaContainer.innerHTML = `<video src="images/history/${visuals}" controls class="history-media" poster="images/pusuban.webp"></video>`;
            } else if (['mp3','wav','m4a'].includes(ext)) {
                mediaContainer.innerHTML = `<audio src="images/history/${visuals}" controls class="history-media" style="width:100%;"></audio>`;
            }
            mediaContainer.querySelector('.history-media')?.style.display = 'block';
        }

        function clearDisplays() {
            titleDisplay.value = '';
            originDisplay.value = '';
            detailsDisplay.value = '';
            mediaContainer.innerHTML = '';
            synth.cancel();
            resetButtons();
            zoomLevel = 1;
            detailsDisplay.style.transform = 'scale(1)';
        }

        function showAlert(alertEl) {
            Array.from(document.querySelectorAll('.alert')).forEach(a => a.style.display = 'none');
            alertEl.style.display = 'block';
            setTimeout(() => alertEl.style.display = 'none', 4000);
        }

        // Speech
        playBtn.onclick = () => speak('play');
        pauseBtn.onclick = () => speak('pause');
        stopBtn.onclick = () => speak('stop');

        function speak(action) {
            const text = detailsDisplay.value;
            if (!text) return;
            if (action === 'play') {
                if (synth.speaking) synth.cancel();
                utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'en-US';
                utterance.rate = 0.85;
                utterance.pitch = 0.95;
                utterance.volume = 0.9;
                synth.speak(utterance);
                toggleSpeechBtns(true);
            } else if (action === 'pause') {
                if (synth.speaking) {
                    synth.pause();
                    pauseBtn.innerHTML = '▶️ Resume';
                } else if (synth.paused) {
                    synth.resume();
                    pauseBtn.innerHTML = '⏸️ Pause';
                }
            } else {
                synth.cancel();
                toggleSpeechBtns(false);
            }
        }

        synth.onend = () => toggleSpeechBtns(false);

        function toggleSpeechBtns(show) {
            playBtn.style.display = show ? 'none' : 'inline-flex';
            pauseBtn.style.display = show ? 'inline-flex' : 'none';
            pauseBtn.innerHTML = '⏸️ Pause';
            stopBtn.style.display = show ? 'inline-flex' : 'none';
        }

        function resetButtons() {
            toggleSpeechBtns(false);
        }

        function startAutoRead() {
            if (detailsDisplay.value) setTimeout(() => speak('play'), 1500);
        }

        // Zoom
        zoomInBtn.onclick = () => {
            zoomLevel = Math.min(zoomLevel + 0.25, 2.5);
            detailsDisplay.style.transform = `scale(${zoomLevel})`;
        };
        zoomOutBtn.onclick = () => {
            zoomLevel = Math.max(zoomLevel - 0.25, 0.75);
            detailsDisplay.style.transform = `scale(${zoomLevel})`;
        };
        detailsDisplay.addEventListener('wheel', e => {
            e.preventDefault();
            if (e.deltaY < 0) zoomInBtn.click();
            else zoomOutBtn.click();
        });

        // Enter search
        searchInput.onkeypress = e => { if (e.key === 'Enter') fetchHistoryData(searchInput.value); };

        // Initial load - perhaps load first entry or popular
        if (titles[0]) fetchHistoryData(titles[0]);
    </script>

    <?php include 'partials/footer.php'; ?>
</body>
</html>
