<?php
// Enable error reporting for debugging
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Enable CORS for AJAX requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Set a longer execution time limit for API calls
set_time_limit(120);

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Include the Gemini controller
    require_once '../Controller/GeminiController.php';

    // Initialize the controller
    $geminiController = new GeminiController();

    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get the request body
        $requestBody = file_get_contents('php://input');
        if (!$requestBody) {
            throw new Exception('Empty request body');
        }

        $requestData = json_decode($requestBody, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON: ' . json_last_error_msg());
        }

        // Process chat message
        if (isset($requestData['message']) && !empty(trim($requestData['message']))) {
            // Log the incoming message for debugging
            error_log('Processing chat message: ' . substr($requestData['message'], 0, 100) . '...');

            // Process the message and get the response
            $response = $geminiController->processMessage($requestData['message']);

            // Log the response for debugging
            error_log('API response: ' . substr($response, 0, 100) . '...');

            // Output the response
            echo $response;
        } else {
            throw new Exception('Missing or empty message parameter');
        }
    } else {
        throw new Exception('Only POST requests are allowed');
    }
} catch (Exception $e) {
    // Log the error
    error_log('API error: ' . $e->getMessage());

    // Return a JSON error response
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'status' => 'error'
    ]);
}
