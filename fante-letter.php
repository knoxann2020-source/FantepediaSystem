<?php
session_start();
include 'partials/header.php';

// Database connection
require 'admin/config/database.php';

// Define Fante letters and authentic Fante words
$fante_letters = [
    'Aa' => ['words' => ['Asa', 'Aba', 'Ama'], 'pronunciation' => 'Ah'],
    'Bb' => ['words' => ['Ba', 'Bese', 'Bɔ'], 'pronunciation' => 'Buh'],
    'Dd' => ['words' => ['Da', 'Dede', 'Dɔ'], 'pronunciation' => 'Duh'],
    'Ee' => ['words' => ['Esi', 'Eku', 'Eba'], 'pronunciation' => 'Eh'],
    'Ɛɛ' => ['words' => ['Ɛyɛ', 'Ɛbɛ', 'Ɛde'], 'pronunciation' => 'Eh'],
    'Ff' => ['words' => ['Firi', 'Fɛ', 'Fa'], 'pronunciation' => 'Fuh'],
    'Gg' => ['words' => ['Gya', 'Gye', 'Ga'], 'pronunciation' => 'Guh'],
    'Hh' => ['words' => ['Hunu', 'Hɔ', 'Ha'], 'pronunciation' => 'Huh'],
    'Ii' => ['words' => ['Ibi', 'Iku', 'Iba'], 'pronunciation' => 'Ih'],
    'Kk' => ['words' => ['Ka', 'Kɔ', 'Kuku'], 'pronunciation' => 'Kuh'],
    'Ll' => ['words' => ['La', 'Lala', 'Lɔ'], 'pronunciation' => 'Luh'],
    'Mm' => ['words' => ['Ma', 'Mama', 'Mɔ'], 'pronunciation' => 'Muh'],
    'Nn' => ['words' => ['Na', 'Nana', 'Nɔ'], 'pronunciation' => 'Nuh'],
    'Oo' => ['words' => ['Osi', 'Oku', 'Oba'], 'pronunciation' => 'Oh'],
    'Ɔɔ' => ['words' => ['Ɔyɛ', 'Ɔbɛ', 'Ɔde'], 'pronunciation' => 'Ooh'],
    'Pp' => ['words' => ['Pa', 'Papa', 'Pɛ'], 'pronunciation' => 'Puh'],
    'Rr' => ['words' => ['Ra', 'Rara', 'Rɔ'], 'pronunciation' => 'Ruh'],
    'Ss' => ['words' => ['Sa', 'Susu', 'Sɔ'], 'pronunciation' => 'Suh'],
    'Tt' => ['words' => ['Ta', 'Tutu', 'Tɔ'], 'pronunciation' => 'Tuh'],
    'Uu' => ['words' => ['Ubi', 'Uku', 'Uba'], 'pronunciation' => 'Uh'],
    'Ww' => ['words' => ['Wa', 'Wɔ', 'Wawa'], 'pronunciation' => 'Wuh'],
    'Yy' => ['words' => ['Ya', 'Yɛ', 'Yaya'], 'pronunciation' => 'Yuh'],
    'Zz' => ['words' => ['Za', 'Zaza', 'Zɔ'], 'pronunciation' => 'Zuh']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fante Letter Learning - Fantepedia</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Additional styles for fante-letter.php */
        .fante-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            background: rgba(255,255,255,0.05);
            border-radius: var(--card-border-radius-4);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            margin-top: 8rem;
        }

        .fante-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .fante-letters {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
            background: rgba(255,255,255,0.1);
            padding: 1rem;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);

        }

        .fante-letter-btn {
            background: linear-gradient(135deg, var(--color-primary), var(--color-primary-variant));
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 50%;
            font-size: 1.5rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;

        }

        .fante-letter-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            background-color: var(--color-green-light);

        }

        .fante-letter-btn:active {
            transform: scale(0.95);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
             background: var(--color-green);
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .fante-display {
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);

        }

        .fante-textbox {
            width: 100%;
            min-height: 150px;
            padding: 1rem;
            border-radius: var(--card-border-radius-4);
            border: 2px solid var(--color-primary);
            background: rgba(255,255,255,0.9);
            color: var(--color-gray-900);
            font-size: 1.1rem;
            resize: vertical;
            margin-bottom: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            backdrop-filter: blur(5px);

        }

        .fante-pronunciation {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--color-primary);
            margin-bottom: 1rem;
            min-height: 30px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);

        }

        .audio-controls {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-bottom: 2rem;
            background: rgba(255,255,255,0.1);
            padding: 1rem;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);

        }

        .audio-btn {
            background: var(--color-purple);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 25px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            min-width: 100px;
            font-weight: bold;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            letter-spacing: 1px;
            backdrop-filter: blur(5px);

        }

        .audio-btn:hover {
            background: var(--color-green-light);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
            border-radius: var(--card-border-radius-4);
            backdrop-filter: blur(7px);
        }

        .audio-btn:active {
            transform: translateY(0);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            background: var(--color-green);
            animation: pulse 1s infinite;

        }

        .audio-btn:disabled {
            background: var(--color-gray-700);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;

        }

        .fante-words {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
            background: rgba(255,255,255,0.1);
            padding: 1rem;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            border-radius: var(--card-border-radius-4);
            font-size: 1.2rem;
            color: var(--color-gray-900);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;

        }

        .fante-word {
            background: rgba(255,255,255,0.8);
            padding: 1rem;
            border-radius: var(--card-border-radius-4);
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            font-weight: bold;
            backdrop-filter: blur(5px);

        }

        .fante-word:hover {
            background: var(--color-primary-light);
            border-color: var(--color-primary);
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            backdrop-filter: blur(7px);

        }

        .fante-word.active {
            background: var(--color-green-light);
            border-color: var(--color-green);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
            backdrop-filter: blur(7px);

        }

        @media (max-width: 768px) {
            .fante-letters {
                grid-template-columns: repeat(auto-fit, minmax(60px, 1fr));
            }

            .fante-letter-btn {
                width: 60px;
                height: 60px;
                font-size: 1.2rem;

            }

            .audio-controls {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>

<div class="fante-container">
    <div class="fante-header">
        <h1>Fante Letter Learning</h1>
        <p>Click on a Fante letter to hear its pronunciation and see example words</p>
    </div>

    <div class="fante-letters">
        <?php foreach ($fante_letters as $letter => $data): ?>
            <button class="fante-letter-btn" data-letter="<?php echo $letter; ?>" data-words="<?php echo htmlspecialchars(json_encode($data['words'])); ?>" data-pronunciation="<?php echo $data['pronunciation']; ?>">
                <?php echo $letter; ?>
            </button>
        <?php endforeach; ?>
    </div>

    <div class="fante-display">
        <h3>Selected Letter Information</h3>
        <div class="fante-pronunciation" id="pronunciation-display">Click a letter to hear its pronunciation</div>
        <textarea class="fante-textbox" id="words-display" readonly placeholder="Example words will appear here..."></textarea>

        <div class="audio-controls">
            <button class="audio-btn" id="play-btn" disabled>Play Pronunciation</button>
            <button class="audio-btn" id="pause-btn" disabled>Pause</button>
            <button class="audio-btn" id="stop-btn" disabled>Stop</button>
        </div>

        <div class="fante-words" id="words-list">
            <!-- Words will be populated by JavaScript -->
        </div>
    </div>
</div>

<script src="js/fante-letter.js"></script>

<?php
include 'partials/footer.php';
?>
</body>
</html>
