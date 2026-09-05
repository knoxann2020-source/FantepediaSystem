-- Demo video entry for music-dance.php test (run in phpMyAdmin)
INSERT INTO fante_music_dance (category, title, description, image, audio, video, status, created_at) VALUES 
('Music', 'Akwaboah Dance Video Demo', 'Traditional Fante dance with video controls test', NULL, NULL, 'images/music-dance/1776792090_Akwaboah.mp4', 'approved', NOW())
ON DUPLICATE KEY UPDATE video = 'images/music-dance/1776792090_Akwaboah.mp4', status='approved';

-- Verify
SELECT id, title, video, status FROM fante_music_dance WHERE title LIKE '%Akwaboah%';
