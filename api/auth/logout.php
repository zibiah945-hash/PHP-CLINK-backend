<?php
/**
 * CLINIK Logout API
 * User logout endpoint
 */

require_once '../../config/database.php';
require_once '../../includes/cors.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

// Check authentication
$user = Auth::checkAuth();

// Log the logout
logMessage("User logged out: " . $user['username'], 'INFO');

// Destroy session
Auth::destroySession();

sendSuccess('Logout successful');
?>
