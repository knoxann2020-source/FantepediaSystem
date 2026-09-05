<?php
session_start();
include 'partials/header.php';
require 'admin/config/database.php';

// Check if table exists, create if not
$table_check_query = "SHOW TABLES LIKE 'fante_states'";
$table_check_result = mysqli_query($connection, $table_check_query);

if (mysqli_num_rows($table_check_result) == 0) {
    // Table doesn't exist, create it
    $create_table_query = "CREATE TABLE IF NOT EXISTS fante_states (
        id INT AUTO_INCREMENT PRIMARY KEY,
        state_name VARCHAR(255) NOT NULL,
        details TEXT NOT NULL,
        video VARCHAR(255),
        latitude DECIMAL(10, 8),
        longitude DECIMAL(11, 8),
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        user_id INT,
        admin_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    if (!mysqli_query($connection, $create_table_query)) {
        die("Error creating table: " . mysqli_error($connection));
    }
}

// Fetch approved states entries with new fields
$query = "SELECT * FROM fante_states WHERE status = 'approved' ORDER BY state_name ASC";
$result = mysqli_query($connection, $query);
$states = [];
while ($row = mysqli_fetch_assoc($result)) {
    $states[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fante States</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .states-container {
            max-width: 1400px;
            margin: 6rem auto 0 auto;
            padding: 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border-radius: 30px;
            backdrop-filter: blur(10px);
            border: 5px solid rgba(38, 168, 45, 0.2);
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
            margin-bottom: 2rem;
        }

        .states-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .state-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
            color: #333;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .state-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }

        .state-name {
            font-size: 1.8rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 1rem;
            border-bottom: 3px solid #3498db;
            padding-bottom: 0.5rem;
        }

        .state-details {
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            color: #555;
        }

        .state-video {
            width: 100%;
            border-radius: 10px;
            margin-bottom: 1rem;
            max-height: 200px;
            box-shadow: inset 0 0 10px rgba(0,0,0,0.1);

        }

        .state-map {
            width: 100%;
            height: 200px;
            border-radius: 10px;
            border: 2px solid #ddd;
            margin-top: 1rem;
            box-shadow: inset 0 0 10px rgba(0,0,0,0.1);


        }

        .audio-controls {
            margin-top: 1rem;
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .audio-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background 0.3s ease;
        }

        .audio-btn:hover {
            background: #2980b9;
        }

        .related-links {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #eee;
            border-radius: 10px;
             background: rgba(255, 255, 255, 0.9);
             padding: 1rem;
             box-shadow: inset 0 0 10px rgba(0,0,0,0.05);
             
        }

        .related-links h3 {
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .link-item {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            text-decoration: none;
            color: #3498db;
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }

        .link-item:hover {
            background: #3498db;
            color: white;
            transform: translateY(-2px);
        }

        .no-states {
            text-align: center;
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 3rem;
        }

        @media (max-width: 768px) {
            .states-container {
                margin: 4rem 1rem 0 1rem;
                padding: 1rem;
            }

            .search-section h1 {
                font-size: 2rem;
            }

            .states-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .state-card {
                padding: 1.5rem;
            }
        }

        /* Dark Mode Support */
        body.dark-mode .states-container {
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
        }

        body.dark-mode .search-section h1,
        body.dark-mode .search-section p {
            color: #e5e7eb;
        }

        body.dark-mode .state-card {
            background: #1f2937;
            color: #e5e7eb;
        }

        body.dark-mode .state-name {
            color: #e5e7eb;
            border-bottom-color: #667eea;
        }

        body.dark-mode .state-details {
            color: #9ca3af;
        }

        body.dark-mode .audio-btn {
            background: #667eea;
        }

        body.dark-mode .audio-btn:hover {
            background: #7c8ff5;
        }

        body.dark-mode .related-links {
            background: rgba(31, 41, 55, 0.9);
        }

        body.dark-mode .related-links h3 {
            color: #e5e7eb;
        }

        body.dark-mode .link-item {
            background: #374151;
            color: #e5e7eb;
            border-color: #4b5563;
        }

        body.dark-mode .link-item:hover {
            background: #667eea;
            color: white;
        }

        body.dark-mode .no-states {
            color: #9ca3af;
        }

        body.dark-mode .interactive-map {
            background: rgba(31, 41, 55, 0.95);
            border-color: #374151;
        }

        body.dark-mode .map-header h3 {
            color: #e5e7eb;
        }

        body.dark-mode #state-selector {
            background: #374151;
            color: #e5e7eb;
            border-color: #4b5563;
        }

        body.dark-mode .interactive-map-canvas {
            background: #374151;
            border-color: #4b5563;
        }
    </style>
</head>
<body>

<section class="states-container">
    <div class="search-section">
        <h1>Explore Fante States</h1>
        <p>Discover the rich history and culture of the 15 traditional Fante states that form the backbone of Fante heritage.</p>
    </div>

    <!-- Interactive Map Section -->
    <section class="interactive-map">
        <div class="container interactive-map__container">
            <div class="map-header">
                <h3>Find a Fante State on the Map</h3>
                <select id="state-selector">
                    <option value="">Select a Fante State</option>
                    <?php 
                    $state_query = "SELECT state_name, latitude, longitude FROM fante_states WHERE status = 'approved' ORDER BY state_name";
                    $state_result = mysqli_query($connection, $state_query);
                    while ($state = mysqli_fetch_assoc($state_result)): 
                    ?>
                    <option value="<?= htmlspecialchars($state['state_name']) ?>" data-lat="<?= $state['latitude'] ?? '5.6148' ?>" data-lng="<?= $state['longitude'] ?? '-0.2057' ?>"><?= htmlspecialchars($state['state_name']) ?></option>
                    <?php endwhile; ?>
                    <?php if (empty($states)): ?>
                    <option value="Mankessim" data-lat="5.2833" data-lng="-1.0167">Mankessim (fallback)</option>
                    <?php endif; ?>
                </select>
            </div>
            <div id="interactive-map" class="interactive-map-canvas"></div>
        </div>
    </section>

    <?php if (empty($states)): ?>
        <div class="no-states">
            <p>No Fante States entries available yet. Check back soon!</p>
        </div>
    <?php else: ?>
        <div class="states-grid">
            <?php foreach ($states as $state): ?>
                <div class="state-card">
                    <h2 class="state-name"><?= htmlspecialchars($state['state_name']) ?></h2>
                    <div class="state-details">
                        <?= nl2br(htmlspecialchars($state['details'])) ?>
                    </div>

                    <?php if ($state['video']): ?>
                        <video class="state-video" controls>
                            <source src="images/<?= htmlspecialchars($state['video']) ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    <?php endif; ?>

                    <div class="audio-controls">
                        <button class="audio-btn" onclick="speakText('<?= htmlspecialchars(addslashes($state['details']), ENT_QUOTES) ?>', '<?= htmlspecialchars($state['state_name']) ?>')">
                            🔊 Listen to Details
                        </button>
                        <button class="audio-btn" onclick="stopSpeech()">⏹️ Stop</button>
                    </div>

                    <?php if ($state['latitude'] && $state['longitude']): ?>
                        <div id="map-<?= $state['id'] ?>" class="state-map"></div>
                        <script>
                            function initMap<?= $state['id'] ?>() {
                                const location = { lat: <?= floatval($state['latitude']) ?>, lng: <?= floatval($state['longitude']) ?> };
                                const map = new google.maps.Map(document.getElementById('map-<?= $state['id'] ?>'), {
                                    zoom: 10,
                                    center: location,
                                });
                                const marker = new google.maps.Marker({
                                    position: location,
                                    map: map,
                                    title: '<?= htmlspecialchars(addslashes($state['state_name']), ENT_QUOTES) ?>'
                                });
                            }
                            // Load map when page loads
                            window.addEventListener('load', initMap<?= $state['id'] ?>);
                        </script>
                    <?php endif; ?>

                    <div class="related-links">
                        <h3>Related Resources</h3>
                        <div class="links-grid">
                            <a href="fante-history.php" class="link-item">📚 Fante History</a>
                            <a href="fante-dictionary.php" class="link-item">📖 Fante Dictionary</a>
                            <a href="fante-phonetics.php" class="link-item">🎵 Fante Phonetics</a>
                            <a href="about.php" class="link-item">ℹ️ About Fante Culture</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<script>
// Speech synthesis functionality
let currentUtterance = null;
let interactiveMap;
let interactiveMarker;

function speakText(text, title) {
    // Stop any current speech
    if (speechSynthesis.speaking) {
        speechSynthesis.cancel();
    }

    // Create new utterance
    currentUtterance = new SpeechSynthesisUtterance(`${title}. ${text}`);
    currentUtterance.lang = 'en-US'; // Adjust language if needed
    currentUtterance.rate = 0.8; // Slightly slower for better comprehension
    currentUtterance.pitch = 1;

    // Speak the text
    speechSynthesis.speak(currentUtterance);
}

function stopSpeech() {
    if (speechSynthesis.speaking) {
        speechSynthesis.cancel();
    }
}

// Interactive Map functionality
function initInteractiveMap() {
    // Initialize the interactive map
    interactiveMap = new google.maps.Map(document.getElementById('interactive-map'), {
        center: {lat: 5.6148, lng: -0.2057}, // Default to Accra, Ghana
        zoom: 7,
        mapTypeControl: true,
        streetViewControl: false,
        fullscreenControl: true
    });

    // Add click listener to place markers
    interactiveMap.addListener('click', function(event) {
        placeInteractiveMarker(event.latLng);
    });

    // Handle state selector change
    document.getElementById('state-selector').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const stateName = selectedOption.value;

        if (stateName) {
            const lat = parseFloat(selectedOption.getAttribute('data-lat'));
            const lng = parseFloat(selectedOption.getAttribute('data-lng'));

            if (lat && lng) {
                const location = {lat: lat, lng: lng};

                // Center map on selected state
                interactiveMap.setCenter(location);
                interactiveMap.setZoom(10);

                // Place marker
                placeInteractiveMarker(location);

                // Add info window with state details
                const infoWindow = new google.maps.InfoWindow({
                    content: `<div style="max-width: 200px;">
                        <h4 style="margin: 0 0 8px 0; color: #2c3e50;">${stateName}</h4>
                        <p style="margin: 0; font-size: 14px; color: #666;">Click on the map to explore other locations in Ghana.</p>
                    </div>`
                });

                if (interactiveMarker) {
                    infoWindow.open(interactiveMap, interactiveMarker);
                }
            }
        }
    });
}

function placeInteractiveMarker(location) {
    if (interactiveMarker) {
        interactiveMarker.setPosition(location);
    } else {
        interactiveMarker = new google.maps.Marker({
            position: location,
            map: interactiveMap,
            animation: google.maps.Animation.DROP,
            title: 'Selected Location'
        });
    }
}

// Load Google Maps API
window.onload = function() {
    if (typeof google === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initAllMaps';
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
    } else {
        initAllMaps();
    }
};

function initAllMaps() {
    // Initialize interactive map
    initInteractiveMap();

    // Initialize individual state maps if they exist
    <?php if (!empty(array_filter($states, fn($s) => $s['latitude'] && $s['longitude']))): ?>
    // Maps are initialized individually in their respective sections
    <?php endif; ?>
}
</script>

<?php include 'partials/footer.php'; ?>
</body>
</html>
