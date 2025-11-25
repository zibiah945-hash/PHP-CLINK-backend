<?php
/**
 * CLINIK CORS Configuration
 * Cross-Origin Resource Sharing settings
 */

// Get allowed origins from environment or use defaults
$allowedOrigins = getenv('ALLOWED_ORIGINS') 
    ? explode(',', getenv('ALLOWED_ORIGINS'))
    : [
        'http://localhost:3000',
        'http://localhost',
        'http://127.0.0.1:3000',
        'http://127.0.0.1',
        'https://frontend-clink-peol96r07-zibiah945-7736s-projects.vercel.app'
    ];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Allow the origin if it's in the allowed list or matches Vercel pattern
if (in_array($origin, $allowedOrigins) || strpos($origin, '.vercel.app') !== false) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    // Fallback to first allowed origin
    header("Access-Control-Allow-Origin: " . $allowedOrigins[0]);
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 86400"); // 24 hours

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Content type
header("Content-Type: application/json; charset=UTF-8");
?>
