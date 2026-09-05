<?php
/**
 * Download Handler for Fantepedia System
 * Handles downloading content as text/PDF files
 */

session_start();
require 'config/constants.php';
require 'config/database.php';

// Get the action type
$action = isset($_GET['action']) ? $_GET['action'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT) : '';

// Function to sanitize filename
function sanitizeFilename($name) {
    $name = strtolower($name);
    $name = preg_replace('/[^a-z0-9_-]/', '_', $name);
    $name = preg_replace('/_+/', '_', $name);
    return $name;
}

// Function to generate content header
function generateContentHeader($title, $type) {
    $date = date('Y-m-d H:i:s');
    return "=======================================\n";
}

switch ($action) {
    case 'download_post':
        if (empty($id)) {
            die('Invalid request');
        }
        
        $id = mysqli_real_escape_string($connection, $id);
        $query = "SELECT p.*, u.username, u.firstname, u.lastname, c.title as category 
                  FROM posts p 
                  LEFT JOIN users u ON p.user_id = u.id 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.id = $id";
        $result = mysqli_query($connection, $query);
        
        if ($post = mysqli_fetch_assoc($result)) {
            $filename = sanitizeFilename($post['title']) . '.txt';
            $content = "TITLE: " . $post['title'] . "\n";
            $content .= "CATEGORY: " . $post['category'] . "\n";
            $content .= "AUTHOR: " . $post['firstname'] . ' ' . $post['lastname'] . "\n";
            $content .= "DATE: " . date('M d, Y', strtotime($post['date_time'])) . "\n";
            $content .= "=======================================\n\n";
            $content .= "CONTENT:\n\n";
            $content .= $post['body'] . "\n\n";
            $content .= "=======================================\n";
            $content .= "Source: Fantepedia System\n";
            $content .= "Downloaded: " . date('Y-m-d H:i:s') . "\n";
            
            // Output file
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($content));
            echo $content;
        } else {
            die('Post not found');
        }
        break;
        
    case 'download_dictionary':
        $word = isset($_GET['word']) ? mysqli_real_escape_string($connection, $_GET['word']) : '';
        
        if (empty($word)) {
            die('Invalid request');
        }
        
        $query = "SELECT * FROM fante_dictionary WHERE word = '$word' AND status = 'approved'";
        $result = mysqli_query($connection, $query);
        
        if ($entry = mysqli_fetch_assoc($result)) {
            $filename = sanitizeFilename($entry['word']) . '_dictionary.txt';
            $content = "=======================================\n";
            $content .= "FANTE DICTIONARY ENTRY\n";
            $content .= "=======================================\n\n";
            $content .= "WORD: " . $entry['word'] . "\n\n";
            $content .= "MEANING:\n" . $entry['meaning'] . "\n\n";
            
            if (!empty($entry['pronunciation'])) {
                $content .= "PRONUNCIATION: " . $entry['pronunciation'] . "\n\n";
            }
            
            if (!empty($entry['example_sentence'])) {
                $content .= "EXAMPLE: " . $entry['example_sentence'] . "\n\n";
            }
            
            $content .= "=======================================\n";
            $content .= "Source: Fantepedia System - Fante Dictionary\n";
            $content .= "Downloaded: " . date('Y-m-d H:i:s') . "\n";
            
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($content));
            echo $content;
        } else {
            die('Dictionary entry not found');
        }
        break;
        
    case 'download_history':
        if (empty($id)) {
            die('Invalid request');
        }
        
        $id = mysqli_real_escape_string($connection, $id);
        $query = "SELECT * FROM fante_history WHERE id = $id AND status = 'approved'";
        $result = mysqli_query($connection, $query);
        
        if ($entry = mysqli_fetch_assoc($result)) {
            $filename = sanitizeFilename($entry['title']) . '.txt';
            $content = "=======================================\n";
            $content .= "FANTE HISTORY\n";
            $content .= "=======================================\n\n";
            $content .= "TITLE: " . $entry['title'] . "\n\n";
            $content .= "CONTENT:\n\n" . $entry['content'] . "\n\n";
            $content .= "=======================================\n";
            $content .= "Source: Fantepedia System - Fante History\n";
            $content .= "Downloaded: " . date('Y-m-d H:i:s') . "\n";
            
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($content));
            echo $content;
        } else {
            die('History entry not found');
        }
        break;
        
    case 'download_artifact':
        if (empty($id)) {
            die('Invalid request');
        }
        
        $id = mysqli_real_escape_string($connection, $id);
        $query = "SELECT * FROM fante_artifacts WHERE id = $id AND status = 'approved'";
        $result = mysqli_query($connection, $query);
        
        if ($entry = mysqli_fetch_assoc($result)) {
            $filename = sanitizeFilename($entry['name']) . '.txt';
            $content = "=======================================\n";
            $content .= "FANTE ARTIFACT\n";
            $content .= "=======================================\n\n";
            $content .= "NAME: " . $entry['name'] . "\n\n";
            $content .= "DESCRIPTION:\n\n" . $entry['description'] . "\n\n";
            
            if (!empty($entry['historical_significance'])) {
                $content .= "HISTORICAL SIGNIFICANCE:\n\n" . $entry['historical_significance'] . "\n\n";
            }
            
            $content .= "=======================================\n";
            $content .= "Source: Fantepedia System - Fante Artifacts\n";
            $content .= "Downloaded: " . date('Y-m-d H:i:s') . "\n";
            
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($content));
            echo $content;
        } else {
            die('Artifact entry not found');
        }
        break;
        
    case 'download_ceremony':
        if (empty($id)) {
            die('Invalid request');
        }
        
        $id = mysqli_real_escape_string($connection, $id);
        $query = "SELECT * FROM fante_ceremonies WHERE id = $id AND status = 'approved'";
        $result = mysqli_query($connection, $query);
        
        if ($entry = mysqli_fetch_assoc($result)) {
            $filename = sanitizeFilename($entry['title']) . '.txt';
            $content = "=======================================\n";
            $content .= "FANTE CEREMONY\n";
            $content .= "=======================================\n\n";
            $content .= "TITLE: " . $entry['title'] . "\n\n";
            $content .= "DESCRIPTION:\n\n" . $entry['description'] . "\n\n";
            
            if (!empty($entry['significance'])) {
                $content .= "SIGNIFICANCE:\n\n" . $entry['significance'] . "\n\n";
            }
            
            if (!empty($entry['traditions'])) {
                $content .= "TRADITIONS:\n\n" . $entry['traditions'] . "\n\n";
            }
            
            $content .= "=======================================\n";
            $content .= "Source: Fantepedia System - Fante Ceremonies\n";
            $content .= "Downloaded: " . date('Y-m-d H:i:s') . "\n";
            
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($content));
            echo $content;
        } else {
            die('Ceremony entry not found');
        }
        break;
        
    case 'download_state':
        if (empty($id)) {
            die('Invalid request');
        }
        
        $id = mysqli_real_escape_string($connection, $id);
        $query = "SELECT * FROM fante_states WHERE id = $id AND status = 'approved'";
        $result = mysqli_query($connection, $query);
        
        if ($entry = mysqli_fetch_assoc($result)) {
            $filename = sanitizeFilename($entry['name']) . '.txt';
            $content = "=======================================\n";
            $content .= "FANTE STATE INFORMATION\n";
            $content .= "=======================================\n\n";
            $content .= "STATE NAME: " . $entry['name'] . "\n\n";
            
            if (!empty($entry['location'])) {
                $content .= "LOCATION: " . $entry['location'] . "\n\n";
            }
            
            if (!empty($entry['history'])) {
                $content .= "HISTORY:\n\n" . $entry['history'] . "\n\n";
            }
            
            if (!empty($entry['culture'])) {
                $content .= "CULTURE:\n\n" . $entry['culture'] . "\n\n";
            }
            
            $content .= "=======================================\n";
            $content .= "Source: Fantepedia System - Fante States\n";
            $content .= "Downloaded: " . date('Y-m-d H:i:s') . "\n";
            
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($content));
            echo $content;
        } else {
            die('State entry not found');
        }
        break;
        
    default:
        die('Invalid action');
}
?>
