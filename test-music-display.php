<?php
require 'config/database.php';
$search = 'Akwaboah';
$query = "SELECT * FROM fante_music_dance WHERE status = 'approved' AND (title LIKE ? OR description LIKE ?) ORDER BY created_at DESC";
$stmt = mysqli_prepare($connection, $query);
$search_term = '%' . $search . '%';
mysqli_stmt_bind_param($stmt, 'ss', $search_term, $search_term);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
echo "Results for '$search': " . mysqli_num_rows($result) . "\n";
while ($row = mysqli_fetch_assoc($result)) {
    echo "ID: {$row['id']} Title: {$row['title']} Video: " . ($row['video'] ?: 'NULL') . "\n";
}
if (mysqli_num_rows($result) > 0) {
    echo "JS entries would load video player controls in modal.\n";
} else {
    echo "No matching approved entries found.\n";
}
?>
