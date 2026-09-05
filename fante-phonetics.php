<?php
session_start();
include 'partials/header.php';

// Database connection
require 'admin/config/database.php';

// Fetch approved phonetics entries
$query = "SELECT * FROM fante_phonetics WHERE status = 'approved' ORDER BY category, title";
$result = mysqli_query($connection, $query);
$phonetics_entries = [];
while ($row = mysqli_fetch_assoc($result)) {
    $phonetics_entries[$row['category']][] = $row;
}

// Default videos mapping
$default_videos = [
    'Fante Alphabets' => 'Alphabets.mp4',
    'Akan Proverbs' => 'Akan Proverbs And Their Meaning.mp4',
    'Fante Numbers' => 'fante alpha.mp4', // Updated to match existing file
    'Names of Months' => 'fante alpha.mp4', // Updated to match existing file
    'Names of Objects' => 'fante alpha.mp4', // Updated to match existing file
    'Days of the Week' => 'Days.mp4',
    'Names of Animals' => 'Animal Namez.mp4',
    'Regions in Ghana' => '16regions.mp4'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fante Phonetics</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .phonetics-container {
            display: flex;
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem;
            gap: 2rem;
            border-radius: var(--card-border-radius, 15px);
            background: rgba(103, 69, 226, 0.9);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            flex-wrap: wrap;
            justify-content: center;
            backdrop-filter: blur(10px);
            
        }
        .buttons-section {
            flex: 1;
            background: rgba(255,255,255,0.9);
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(18, 175, 175, 0.1);
            min-width: 250px;
            align-items: center;
            
        }
        .video-section {
            flex: 2;
            background: rgba(255,255,255,0.9);
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(125, 151, 31, 0.61);
            min-width: 300px;

        }
        .phonetics-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 1rem;

        }
        .phonetics-btn {
            background: linear-gradient(135deg, var(--color-bg), var(--color-primary-variant));
            color: black;
            border: none;
            padding: 1rem;
            border-radius: 10px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }
        .phonetics-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: var(--color-purple-dark);

        }
        .phonetics-btn.active {
            background: linear-gradient(135deg, var(--color-green), var(--color-green-variant));
        }
        .video-container {
            position: relative;
            background: #000;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 1rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);

        }
        .video-player {
            width: 100%;
            height: auto;
            display: block;
            border-radius: 10px;
            background: #3b10b1;
            box-shadow: 4 5px 15px rgba(0,0,0,0.3);
        }
        .video-controls {
            display: flex;
            justify-content: center;
            gap: 1rem;
            padding: 1rem;
            background: rgba(176, 211, 22, 0.8);
            border-radius: 10px;
            margin-top: 1rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);

        }
        .control-btn {
            background: var(--color-primary);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;

        }
        .control-btn:hover {
            background: var(--color-primary-variant);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);

        }
        .alert {
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 5px;
            display: none;
            font-weight: bold;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);

        }
        .alert.success {
            background: var(--color-green-light);
            color: var(--color-green);

        }
        .alert.error {
            background: var(--color-red-light);
            color: var(--color-red);

        }
        .entries-list {
            margin-top: 2rem;
            max-height: 300px;
            overflow-y: auto;
            border-top: 2px solid var(--color-primary);
            padding-top: 1rem;

        }
        .entry-item {
            background: rgba(255,255,255,0.8);
            padding: 1rem;
            margin: 0.5rem 0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);

        }
        .entry-item:hover {
            background: var(--color-primary-light);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);

        }

        .phonetics h1, .phonetics p {
            text-align: center;
            color: var(--color-primary-dark);
        }
        .phonetics h1 {
            margin-bottom: 0.5rem;
            align-items: center;

        }
        .phonetics p {
            margin-bottom: 2rem;
            align-items: center;
        }
        .phonetics .container {
            background: rgba(77, 116, 134, 0.9);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 40px 60px rgba(0,0,0,0.1);
            backdrop-filter: blur(250px);

        }


        @media (max-width: 768px) {
            .phonetics-container {
                flex-direction: column;
                padding: 0.5rem;
                gap: 0.5rem;
                width: 100%;
                box-sizing: border-box;
            }
            .buttons-section, .video-section {
                padding: 1rem;
                min-width: unset;
                width: 100%;
                flex: 1 1 100%;
                box-sizing: border-box;
            }
            .phonetics-buttons {
                gap: 0.5rem;
            }
            .phonetics-btn {
                padding: 0.8rem;
                font-size: 1rem;
            }
            .video-controls {
                flex-wrap: wrap;
                padding: 0.5rem;
                gap: 0.5rem;
            }
            .control-btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.9rem;
            }
        }

        /* Dark Mode Support */
        body.dark-mode .phonetics-container {
            background: rgba(31, 41, 55, 0.9);
        }

        body.dark-mode .buttons-section {
            background: #1f2937;
        }

        body.dark-mode .buttons-section h3 {
            color: #e5e7eb;
        }

        body.dark-mode .video-section {
            background: #1f2937;
        }

        body.dark-mode .video-section h3 {
            color: #e5e7eb;
        }

        body.dark-mode .phonetics-btn {
            background: linear-gradient(135deg, #374151 0%, #4b5563 100%);
            color: #e5e7eb;
        }

        body.dark-mode .phonetics-btn:hover {
            background: linear-gradient(135deg, #4b5563 0%, #6b7280 100%);
        }

        body.dark-mode .phonetics-btn.active {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        }

        body.dark-mode .video-controls {
            background: rgba(31, 41, 55, 0.8);
        }

        body.dark-mode .control-btn {
            background: #667eea;
        }

        body.dark-mode .control-btn:hover {
            background: #7c8ff5;
        }

        body.dark-mode .entry-item {
            background: #374151;
        }

        body.dark-mode .entry-item:hover {
            background: #4b5563;
        }

        body.dark-mode .entry-item h4 {
            color: #e5e7eb;
        }

        body.dark-mode .entry-item p {
            color: #9ca3af;
        }

        body.dark-mode .alert.success {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
        }

        body.dark-mode .alert.error {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
        }

        body.dark-mode .phonetics h1,
        body.dark-mode .phonetics p {
            color: #e5e7eb;
        }

        body.dark-mode .phonetics .container {
            background: rgba(31, 41, 55, 0.9);
        }

        /* PlayLab Link Styles */
        .playlab-section {
            text-align: center;
            margin: 2rem 0;
            padding: 2rem;
            background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(255,255,255,0.85));
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .playlab-section h3 {
            margin-bottom: 0.5rem;
            font-size: 1.5rem;
        }

        .playlab-btn {
            display: inline-block;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white !important;
            padding: 1.2rem 2.5rem;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.4);
            border: none;
            position: relative;
            overflow: hidden;
        }

        .playlab-btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(79, 70, 229, 0.6);
            background: linear-gradient(135deg, #7c3aed, #ec4899);
        }

        .playlab-btn:active {
            transform: translateY(-2px);
        }

        body.dark-mode .playlab-section {
            background: linear-gradient(135deg, rgba(55,65,81,0.95), rgba(75,85,99,0.85));
            border-color: rgba(255,255,255,0.1);
        }

        body.dark-mode .playlab-section h3,
        body.dark-mode .playlab-section p {
            color: #f9fafb;
        }

        body.dark-mode .playlab-btn {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
        }

        body.dark-mode .playlab-btn:hover {
            background: linear-gradient(135deg, #8b5cf6, #ec4899);
            box-shadow: 0 15px 35px rgba(99, 102, 241, 0.6);
        }

        @media (max-width: 768px) {
            .playlab-section {
                margin: 1rem 0;
                padding: 1.5rem;
            }
            .playlab-btn {
                padding: 1rem 2rem;
                font-size: 1.1rem;
            }
        }
    </style>

</head>
<body>
    <section class="phonetics section__extra-margin">
        <div class="container">
            <h1>Fante Phonetics</h1>
            <p>Explore Fante language through interactive videos and audio content.</p>

            <!-- PlayLab Interactive Learning Link -->
            <div class="playlab-section">
                <h3 style="color: var(--color-primary-dark);">🚀 Practice Fante Interactively!</h3>
                <p style="margin-bottom: 1.5rem;">Level up with this dedicated Fante language course on PlayLab.</p>
                <a href="https://www.playlab.ai/project/cmddltptf0qqfj70ue5jgr0cp" target="_blank" class="playlab-btn">
                    🎯 Learn Fante on PlayLab
                </a>
            </div>

            <div class="phonetics-container">

                <!-- Left Section: Buttons -->
                <div class="buttons-section">
                    <h3>Select Category</h3>
                    <div class="phonetics-buttons">
                        <button class="phonetics-btn" data-category="Fante Alphabets">Fante Alphabets</button>
                        <button class="phonetics-btn" data-category="Akan Proverbs">Akan Proverbs</button>
                        <button class="phonetics-btn" data-category="Fante Numbers">Fante Numbers</button>
                        <button class="phonetics-btn" data-category="Names of Months">Names of Months</button>
                        <button class="phonetics-btn" data-category="Names of Objects">Names of Objects</button>
                        <button class="phonetics-btn" data-category="Days of the Week">Days of the Week</button>
                        <button class="phonetics-btn" data-category="Names of Animals">Names of Animals</button>
                        <button class="phonetics-btn" data-category="Regions in Ghana">Regions in Ghana</button>
                    </div>
                </div>

                <!-- Right Section: Video Player -->
                <div class="video-section">
                    <h3>Video Player</h3>
                    <div id="alert" class="alert"></div>
                    <div class="video-container">
                        <video id="videoPlayer" class="video-player" controls>
                            <source id="videoSource" src="" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                    <div class="video-controls">
                        <button id="playBtn" class="control-btn">Play</button>
                        <button id="pauseBtn" class="control-btn">Pause</button>
                        <button id="stopBtn" class="control-btn">Stop</button>
                        <button id="fullscreenBtn" class="control-btn">Fullscreen</button>
                    </div>

                    <!-- Entries List -->
                    <div id="entriesList" class="entries-list">
                        <!-- Entries will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        const videoPlayer = document.getElementById('videoPlayer');
        const videoSource = document.getElementById('videoSource');
        const alertDiv = document.getElementById('alert');
        const entriesList = document.getElementById('entriesList');
        const buttons = document.querySelectorAll('.phonetics-btn');

        // PHP data
        const phoneticsData = <?php echo json_encode($phonetics_entries); ?>;
        // Default videos removed: DB uploads should be used when available.
        const defaultVideos = <?php echo json_encode($default_videos); ?>;

        // Video controls
        document.getElementById('playBtn').addEventListener('click', () => {
            videoPlayer.play();
        });

        document.getElementById('pauseBtn').addEventListener('click', () => {
            videoPlayer.pause();
        });

        document.getElementById('stopBtn').addEventListener('click', () => {
            videoPlayer.pause();
            videoPlayer.currentTime = 0;
        });

        document.getElementById('fullscreenBtn').addEventListener('click', () => {
            if (videoPlayer.requestFullscreen) {
                videoPlayer.requestFullscreen();
            } else if (videoPlayer.webkitRequestFullscreen) {
                videoPlayer.webkitRequestFullscreen();
            } else if (videoPlayer.msRequestFullscreen) {
                videoPlayer.msRequestFullscreen();
            }
        });

        // Check video support
        function checkVideoSupport(videoSrc) {
            const video = document.createElement('video');
            return video.canPlayType('video/mp4') !== '';
        }

        // Load video
        function loadVideo(category) {
            let videoSrc = '';

            // Prefer DB uploads for this category (supports multiple videos via entries list).
            if (phoneticsData[category]) {
                const firstWithVideo = phoneticsData[category].find(e => e.video);
                if (firstWithVideo && firstWithVideo.video) {
                    videoSrc = 'images/phonetics-editor/' + firstWithVideo.video;
                }
            }

            // Fallback only if no DB video exists for this category.
            if (!videoSrc && defaultVideos[category]) {
                videoSrc = 'images/' + defaultVideos[category];
            }


            if (videoSrc) {
                if (checkVideoSupport(videoSrc)) {
                    videoSource.src = videoSrc;
                    videoPlayer.load();
                    showAlert('Video loaded successfully!', 'success');
                } else {
                    showAlert('This video format is not supported by your browser.', 'error');
                }
            } else {
                showAlert('No video available for this category.', 'error');
            }
        }

        // Show alert
        function showAlert(message, type) {
            alertDiv.textContent = message;
            alertDiv.className = `alert ${type}`;
            alertDiv.style.display = 'block';
            setTimeout(() => {
                alertDiv.style.display = 'none';
            }, 5000);
        }

        // Load entries
        function loadEntries(category) {
            entriesList.innerHTML = '';
            if (phoneticsData[category]) {
                phoneticsData[category].forEach(entry => {
                    const entryDiv = document.createElement('div');
                    entryDiv.className = 'entry-item';
                    entryDiv.innerHTML = `
                        <h4>${entry.title}</h4>
                        <p>${entry.description || 'No description available.'}</p>
                    `;
                    entryDiv.addEventListener('click', () => {
                        if (entry.video) {
                            videoSource.src = 'images/phonetics-editor/' + entry.video;
                            videoPlayer.load();
                            showAlert('Video loaded successfully!', 'success');
                        } else {
                            showAlert('No video available for this entry.', 'error');
                        }
                    });
                    entriesList.appendChild(entryDiv);
                });
            } else {
                entriesList.innerHTML = '<p>No entries available for this category.</p>';
            }
        }

        // Button click handlers
        buttons.forEach(button => {
            button.addEventListener('click', () => {
                buttons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                const category = button.dataset.category;
                loadVideo(category);
                loadEntries(category);
            });
        });

        // Load first category by default
        if (buttons.length > 0) {
            buttons[0].click();
        }
    </script>

    <?php include 'partials/footer.php'; ?>
</body>
</html>
