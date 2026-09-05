<?php
/**
 * AI Assistant API for Fantepedia System
 * 
 * This file handles all AI-related requests including:
 * - Chatbot conversations
 * - Search enhancement
 * - Translation (Fante to English)
 * - Content assistance
 * - Sentiment analysis
 * - Context-aware responses
 * 
 * Security features:
 * - Input validation and sanitization
 * - Rate limiting
 * - XSS protection
 * - Request size limits
 */

// Secure session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_start();

require 'config/database.php';
require 'config/ai-config.php';

// Security headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Rate limiting
$rateLimitFile = sys_get_temp_dir() . '/ai_rate_' . md5($_SERVER['REMOTE_ADDR'] ?? 'unknown');
$maxRequestsPerMinute = 30;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check request size (max 10KB)
$inputSize = strlen(file_get_contents('php://input'));
if ($inputSize > 10000) {
    echo json_encode(['success' => false, 'message' => 'Request too large.']);
    exit();
}

// Rate limiting check
$currentTime = time();
if (file_exists($rateLimitFile)) {
    $rateData = json_decode(file_get_contents($rateLimitFile), true);
    if ($rateData && ($currentTime - $rateData['first_request']) < 60) {
        $rateData['count']++;
        if ($rateData['count'] > $maxRequestsPerMinute) {
            echo json_encode(['success' => false, 'message' => 'Rate limit exceeded.']);
            exit();
        }
    } else {
        $rateData = ['first_request' => $currentTime, 'count' => 1];
    }
} else {
    $rateData = ['first_request' => $currentTime, 'count' => 1];
}
file_put_contents($rateLimitFile, json_encode($rateData));

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

$response = ['success' => false, 'message' => '', 'data' => null];

// Only allow POST
if ($method !== 'POST') {
    $response['message'] = 'Only POST requests allowed';
    echo json_encode($response);
    exit();
}

try {
    // Get action - don't sanitize with strict mode for action names with underscores
    $action = isset($input['action']) ? trim($input['action']) : '';
    
    // Whitelist allowed actions
    $allowedActions = ['chat', 'search', 'translate', 'content_assist', 'get_suggestions', 'get_conversations', 'load_conversation', 'delete_conversation', 'sentiment_analysis', 'get_context'];
    if (!in_array($action, $allowedActions)) {
        $response['message'] = 'Invalid action specified';
        echo json_encode($response);
        exit();
    }
    
    switch ($action) {
        case 'chat':
            $response = handleChat($input);
            break;
        case 'search':
            $response = handleSearchEnhancement($input);
            break;
        case 'translate':
            $response = handleTranslation($input);
            break;
        case 'content_assist':
            $response = handleContentAssistant($input);
            break;
        case 'get_suggestions':
            $response = handleSuggestions($input);
            break;
        case 'get_conversations':
            $response = handleGetConversations($input);
            break;
        case 'load_conversation':
            $response = handleLoadConversation($input);
            break;
        case 'delete_conversation':
            $response = handleDeleteConversation($input);
            break;
        case 'sentiment_analysis':
            $response = handleSentimentAnalysis($input);
            break;
        case 'get_context':
            $response = handleGetContext($input);
            break;
        default:
            $response['message'] = 'Invalid action specified';
    }
} catch (Exception $e) {
    error_log('AI Error: ' . substr($e->getMessage(), 0, 100));
    $response['message'] = 'An error occurred. Please try again later.';
}

echo json_encode($response);
exit();

/**
 * Handle chatbot conversation with enhanced context
 */
function handleChat($input) {
    $message = sanitizeInput($input['message'] ?? '');
    $conversationId = sanitizeInput($input['conversation_id'] ?? '', true);
    
    if (empty($message)) {
        return ['success' => false, 'message' => 'Please provide a message'];
    }
    
    // Get conversation history
    $history = getConversationHistory($conversationId);
    
    // Build messages for AI
    $messages = [
        ['role' => 'system', 'content' => AI_CHATBOT_SYSTEM_PROMPT]
    ];
    
    // Add conversation history
    foreach ($history as $msg) {
        $messages[] = $msg;
    }
    
    // Add current message
    $messages[] = ['role' => 'user', 'content' => $message];
    
    // Call AI API
    $aiResponse = callAIApi($messages);
    
    // Save conversation
    $newConversationId = saveConversation($conversationId, $message, $aiResponse);
    
    return [
        'success' => true,
        'message' => $aiResponse,
        'conversation_id' => $newConversationId,
        'timestamp' => time()
    ];
}

/**
 * Handle search enhancement
 */
function handleSearchEnhancement($input) {
    $query = sanitizeInput($input['query'] ?? '');
    
    if (empty($query)) {
        return ['success' => false, 'message' => 'Please provide a search query'];
    }
    
    $prompt = AI_SEARCH_SYSTEM_PROMPT . "\n\nUser's search query: " . $query . "\n\nProvide relevant keywords and related terms:";
    
    $messages = [
        ['role' => 'system', 'content' => $prompt],
        ['role' => 'user', 'content' => 'Enhance this search: ' . $query]
    ];
    
    $enhancedQuery = callAIApi($messages);
    
    return [
        'success' => true,
        'original_query' => $query,
        'enhanced_query' => $enhancedQuery,
        'suggestions' => array_map('trim', explode(',', $enhancedQuery))
    ];
}

/**
 * Handle translation request with multiple language support
 */
function handleTranslation($input) {
    $text = sanitizeInput($input['text'] ?? '');
    $from = sanitizeInput($input['from'] ?? 'en');
    $to = sanitizeInput($input['to'] ?? 'fante');
    
    if (empty($text)) {
        return ['success' => false, 'message' => 'Please provide text to translate'];
    }
    
    // Validate languages
    $allowedLangs = ['en', 'fante', 'twi', 'ga'];
    if (!in_array($from, $allowedLangs) || !in_array($to, $allowedLangs)) {
        return ['success' => false, 'message' => 'Invalid language specified'];
    }
    
    $direction = $from . ' to ' . $to;
    $prompt = AI_TRANSLATION_SYSTEM_PROMPT . "\n\nTranslate from " . $direction . ":\n\n" . $text;
    
    $messages = [
        ['role' => 'system', 'content' => $prompt],
        ['role' => 'user', 'content' => 'Translate: ' . $text]
    ];
    
    $translation = callAIApi($messages);
    
    return [
        'success' => true,
        'original' => $text,
        'translation' => $translation,
        'from' => $from,
        'to' => $to
    ];
}

/**
 * Handle content assistance
 */
function handleContentAssistant($input) {
    $content = sanitizeInput($input['content'] ?? '');
    $type = sanitizeInput($input['type'] ?? 'general');
    $action = sanitizeInput($input['assist_action'] ?? 'improve');
    
    if (empty($content)) {
        return ['success' => false, 'message' => 'Please provide content to assist with'];
    }
    
    $prompts = [
        'improve' => 'Improve the following content for clarity and engagement:',
        'expand' => 'Expand on the following content with more details:',
        'check' => 'Check the following content for accuracy and suggest corrections:',
        'summarize' => 'Summarize the following content:'
    ];
    
    $prompt = AI_CONTENT_ASSISTANT_SYSTEM_PROMPT . "\n\n" . ($prompts[$action] ?? $prompts['improve']) . "\n\n" . $content;
    
    $messages = [
        ['role' => 'system', 'content' => $prompt],
        ['role' => 'user', 'content' => $content]
    ];
    
    $result = callAIApi($messages);
    
    return [
        'success' => true,
        'original' => $content,
        'result' => $result,
        'type' => $type,
        'action' => $action
    ];
}

/**
 * Handle sentiment analysis
 */
function handleSentimentAnalysis($input) {
    $text = sanitizeInput($input['text'] ?? '');
    
    if (empty($text)) {
        return ['success' => false, 'message' => 'Please provide text to analyze'];
    }
    
    // Simple keyword-based sentiment analysis
    $positiveWords = ['good', 'great', 'excellent', 'amazing', 'wonderful', 'love', 'best', 'happy', 'beautiful', 'helpful', 'interesting', 'awesome'];
    $negativeWords = ['bad', 'terrible', 'awful', 'worst', 'hate', 'sad', 'angry', 'disappointed', 'poor', 'boring', 'wrong', 'useless'];
    
    $textLower = strtolower($text);
    $positiveCount = 0;
    $negativeCount = 0;
    
    foreach ($positiveWords as $word) {
        if (strpos($textLower, $word) !== false) $positiveCount++;
    }
    foreach ($negativeWords as $word) {
        if (strpos($textLower, $word) !== false) $negativeCount++;
    }
    
    if ($positiveCount > $negativeCount) {
        $sentiment = 'positive';
    } elseif ($negativeCount > $positiveCount) {
        $sentiment = 'negative';
    } else {
        $sentiment = 'neutral';
    }
    
    return [
        'success' => true,
        'text' => $text,
        'sentiment' => $sentiment,
        'score' => ($positiveCount - $negativeCount),
        'positive_words' => $positiveCount,
        'negative_words' => $negativeCount
    ];
}

/**
 * Handle get context - returns site context for AI
 */
function handleGetContext($input) {
    return [
        'success' => true,
        'context' => [
            'site_name' => 'Fantepedia',
            'description' => 'Cultural heritage website dedicated to the Fante people of Ghana',
            'categories' => ['History', 'Language', 'Culture', 'Traditions', 'Food', 'Music', 'Ceremonies'],
            'features' => ['Dictionary', 'Phonetics', 'States', 'Artifacts', 'Ceremonies', 'History'],
            'supported_languages' => ['English', 'Fante', 'Twi', 'Ga']
        ]
    ];
}

/**
 * Handle suggestions request
 */
function handleSuggestions($input) {
    $type = sanitizeInput($input['type'] ?? 'topics');
    
    $suggestions = [
        'topics' => [
            'Fante traditional ceremonies',
            'Fante language basics',
            'Fante historical figures',
            'Traditional Fante foods',
            'Fante music and dance',
            'Fante fishing traditions',
            'Fante kente cloth',
            'Fante marketplace culture',
            'Fante religious beliefs',
            'Fante royal ancestry'
        ],
        'titles' => [
            'The Rich Heritage of the Fante People',
            'Understanding Fante Language',
            'Traditional Fante Ceremonies',
            'Fante History Through the Ages',
            'The Art of Fante Kente Weaving',
            'Fante Cuisine: A Culinary Journey',
            'Fante Fishing Traditions',
            'The Fante Royal Kingdom',
            'Fante Music and Dance',
            'Fante Marketplace Culture'
        ],
        'categories' => [
            'History', 'Language', 'Culture', 'Traditions', 'Food', 'Music', 'Ceremonies', 'Arts & Crafts', 'Geography', 'People'
        ]
    ];
    
    return [
        'success' => true,
        'suggestions' => $suggestions[$type] ?? $suggestions['topics']
    ];
}

/**
 * Call AI API with error handling
 */
function callAIApi($messages) {
    // Check if API key is valid using the validation function
    if (!isOpenAIKeyValid()) {
        return getDemoResponse($messages);
    }
    
    $url = 'https://api.openai.com/v1/chat/completions';
    
    $data = [
        'model' => OPENAI_MODEL,
        'messages' => $messages,
        'max_tokens' => OPENAI_MAX_TOKENS,
        'temperature' => OPENAI_TEMPERATURE
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . OPENAI_API_KEY
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($httpCode === 200) {
        $result = json_decode($response, true);
        if (isset($result['choices'][0]['message']['content'])) {
            return $result['choices'][0]['message']['content'];
        }
    }
    
    return 'I apologize, but I encountered an error processing your request. Please try again.';
}

/**
 * Get demo response for testing
 */
function getDemoResponse($messages) {
    $lastMessage = end($messages)['content'] ?? '';
    $lastMessageLower = strtolower($lastMessage);
    
    $demoResponses = [
        'hello' => 'Hello! Welcome to Fantepedia System. How can I help you today?',
        'help' => 'I can help you with: searching for content, translating between English and Fante, and assisting with content creation. What would you like to do?',
        'fante' => 'The Fante (also spelled Mfantse or Fanti) are an ethnic group in Ghana, primarily located in the coastal regions of the Central Region. They speak the a Twi dialect.',
        'history' => 'The Fante language, Fante people have a rich history dating back to the 13th century. They were known for their trading prowess and established the powerful Fante Confederation in the 18th century.',
        'translate' => 'I can help translate between English and Fante (Mfantse). Just send me text you want to translate!',
        'default' => 'Thank you for your message! This is a demo response. To get full AI assistance, please configure your API key in config/ai-config.php'
    ];
    
    foreach ($demoResponses as $key => $response) {
        if (strpos($lastMessageLower, $key) !== false) {
            return $response;
        }
    }
    
    return $demoResponses['default'];
}

/**
 * Get conversation history
 */
function getConversationHistory($conversationId) {
    if (empty($conversationId)) {
        return [];
    }
    
    $key = 'chat_history_' . preg_replace('/[^a-zA-Z0-9]/', '', $conversationId);
    return $_SESSION[$key] ?? [];
}

/**
 * Save conversation and return conversation ID
 */
function saveConversation($conversationId, $userMessage, $aiMessage) {
    if (empty($conversationId)) {
        $conversationId = generateConversationId();
    }
    
    $key = 'chat_history_' . preg_replace('/[^a-zA-Z0-9]/', '', $conversationId);
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [];
    }
    
    $_SESSION[$key][] = ['role' => 'user', 'content' => $userMessage];
    $_SESSION[$key][] = ['role' => 'assistant', 'content' => $aiMessage];
    
    // Keep only last N messages
    if (count($_SESSION[$key]) > AI_MAX_HISTORY_MESSAGES * 2) {
        $_SESSION[$key] = array_slice($_SESSION[$key], -AI_MAX_HISTORY_MESSAGES * 2);
    }
    
    // Update conversation metadata
    updateConversationMetadata($conversationId, $userMessage);
    
    return $conversationId;
}

/**
 * Update conversation metadata
 */
function updateConversationMetadata($conversationId, $firstMessage) {
    if (!isset($_SESSION['ai_conversations'])) {
        $_SESSION['ai_conversations'] = [];
    }
    
    $found = false;
    foreach ($_SESSION['ai_conversations'] as &$conv) {
        if ($conv['id'] === $conversationId) {
            $conv['timestamp'] = time();
            $conv['last_message'] = substr($firstMessage, 0, 50);
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $_SESSION['ai_conversations'][] = [
            'id' => $conversationId,
            'title' => substr($firstMessage, 0, 40) . (strlen($firstMessage) > 40 ? '...' : ''),
            'last_message' => substr($firstMessage, 0, 50),
            'timestamp' => time(),
            'message_count' => 0
        ];
    }
}

/**
 * Generate unique conversation ID
 */
function generateConversationId() {
    return bin2hex(random_bytes(16));
}

/**
 * Sanitize input
 */
function sanitizeInput($input, $strict = false) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    
    $input = trim($input);
    
    if ($strict) {
        return preg_replace('/[^a-zA-Z0-9_-]/', '', $input);
    }
    
    return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Handle get conversations request
 */
function handleGetConversations($input) {
    $conversations = getAllConversations();
    return ['success' => true, 'conversations' => $conversations];
}

/**
 * Handle load conversation request
 */
function handleLoadConversation($input) {
    $conversationId = sanitizeInput($input['conversation_id'] ?? '', true);
    
    if (empty($conversationId)) {
        return ['success' => false, 'message' => 'Please provide a conversation ID'];
    }
    
    $history = getConversationHistory($conversationId);
    
    if (empty($history)) {
        return ['success' => false, 'message' => 'Conversation not found'];
    }
    
    return [
        'success' => true,
        'conversation_id' => $conversationId,
        'messages' => $history
    ];
}

/**
 * Handle delete conversation request
 */
function handleDeleteConversation($input) {
    $conversationId = sanitizeInput($input['conversation_id'] ?? '', true);
    
    if (empty($conversationId)) {
        return ['success' => false, 'message' => 'Please provide a conversation ID'];
    }
    
    $result = deleteConversation($conversationId);
    
    return [
        'success' => $result,
        'message' => $result ? 'Conversation deleted' : 'Failed to delete conversation'
    ];
}

/**
 * Get all saved conversations
 */
function getAllConversations() {
    $conversations = $_SESSION['ai_conversations'] ?? [];
    
    usort($conversations, function($a, $b) {
        return ($b['timestamp'] ?? 0) - ($a['timestamp'] ?? 0);
    });
    
    return $conversations;
}

/**
 * Delete a conversation
 */
function deleteConversation($conversationId) {
    if (!isset($_SESSION['ai_conversations'])) {
        return false;
    }
    
    $key = 'chat_history_' . preg_replace('/[^a-zA-Z0-9]/', '', $conversationId);
    
    if (isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
    }
    
    $_SESSION['ai_conversations'] = array_filter($_SESSION['ai_conversations'], function($conv) use ($conversationId) {
        return $conv['id'] !== $conversationId;
    });
    
    return true;
}
?>
