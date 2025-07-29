<?php
// Include functions
require_once 'includes/functions.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get request data
$data = json_decode(file_get_contents('php://input'), true);

// Check if message is provided
if (!isset($data['message']) || empty($data['message'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No message provided']);
    exit;
}

// Get user message
$user_message = $data['message'];

// Generate session ID if not exists
if (!isset($_SESSION['chat_session_id'])) {
    $_SESSION['chat_session_id'] = uniqid('chat_', true);
}
$session_id = $_SESSION['chat_session_id'];

// Get user ID if logged in
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Save user message to database
save_chat_message($user_id, $session_id, $user_message, false);

// Get chat history for context
$chat_history = get_chat_history($session_id, 10);

// Format chat history for Gemini API
$formatted_history = [];
foreach ($chat_history as $msg) {
    $formatted_history[] = [
        'role' => $msg['is_bot'] ? 'model' : 'user',
        'parts' => [
            ['text' => $msg['message']]
        ]
    ];
}

// Prepare Gemini API request
$api_key = 'AIzaSyDPbViQ_htxJc-wNI1tq921XE1och48tyo'; // Replace with your actual API key
$model = 'gemini-2.0-flash';
$endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";

// Define system prompt
$system_prompt = "You are a helpful fashion assistant for Velvet Vogue, an online fashion store. 
Your name is Velvet Assistant. You help customers with product recommendations, sizing advice, 
fashion tips, and general inquiries about the store. You are friendly, professional, and knowledgeable 
about fashion trends. You can assist with finding products, suggesting outfits, and providing 
information about shipping, returns, and store policies. If asked about specific product details 
or inventory that you don't have information about, kindly suggest the customer to browse the 
website or contact customer service.";

// Add system prompt to history
array_unshift($formatted_history, [
    'role' => 'model',
    'parts' => [
        ['text' => $system_prompt]
    ]
]);

// Prepare request data
$request_data = [
    'contents' => $formatted_history,
    'generationConfig' => [
        'temperature' => 0.7,
        'topK' => 40,
        'topP' => 0.95,
        'maxOutputTokens' => 1024,
    ]
];

// Initialize cURL session
$ch = curl_init($endpoint);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

// Execute cURL request
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Close cURL session
curl_close($ch);

// Check for errors
if ($http_code !== 200) {
    // Log error
    error_log("Gemini API Error: " . $response);
    
    // Fallback response
    $bot_response = "I'm sorry, I'm having trouble connecting to my brain right now. Please try again later or contact customer service for immediate assistance.";
} else {
    // Parse response
    $response_data = json_decode($response, true);
    
    // Extract bot response
    if (isset($response_data['candidates'][0]['content']['parts'][0]['text'])) {
        $bot_response = $response_data['candidates'][0]['content']['parts'][0]['text'];
    } else {
        // Fallback response if structure is unexpected
        $bot_response = "I'm sorry, I couldn't process your request properly. Please try again with a different question.";
    }
}

// Save bot response to database
save_chat_message($user_id, $session_id, $bot_response, true);

// Return response
echo json_encode([
    'response' => $bot_response
]);
?>
