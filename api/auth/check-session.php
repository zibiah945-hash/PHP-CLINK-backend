<?php
/**
 * CLINIK Check Session API
 * Verify user session endpoint
 */

require_once '../../config/database.php';
require_once '../../includes/cors.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed', 405);
}

// Check authentication
$user = Auth::checkAuth();

sendSuccess('Session valid', [
    'user' => [
        'user_id' => $user['user_id'],
        'username' => $user['username'],
        'role' => $user['role'],
        'full_name' => $user['full_name']
    ]
]);
?>
