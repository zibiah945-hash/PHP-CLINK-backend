<?php
/**
 * CLINIK Login API
 * User authentication endpoint
 */

require_once '../../config/database.php';
require_once '../../includes/cors.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

// Get JSON input
$data = getJsonInput();

// Validate required fields
validateRequired($data, ['username', 'password']);

// Sanitize inputs
$username = sanitizeInput($data['username']);
$password = $data['password'];

try {
    // Get database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    // Prepare SQL statement
    $sql = "SELECT user_id, username, password_hash, email, full_name, role, is_active 
            FROM users 
            WHERE username = :username AND is_active = 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        logMessage("Failed login attempt for username: $username", 'WARNING');
        sendError('Invalid username or password', 401);
    }
    
    $user = $stmt->fetch();
    
    // Verify password
    if (!Auth::verifyPassword($password, $user['password_hash'])) {
        logMessage("Failed login attempt for username: $username (wrong password)", 'WARNING');
        sendError('Invalid username or password', 401);
    }
    
    // Start session and set session variables
    Auth::startSession();
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['login_time'] = time();
    
    logMessage("Successful login for user: $username", 'INFO');
    
    // Return success response
    sendSuccess('Login successful', [
        'user' => [
            'user_id' => $user['user_id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'role' => $user['role']
        ]
    ]);
    
} catch (PDOException $e) {
    logMessage("Login error: " . $e->getMessage(), 'ERROR');
    sendError('Login failed due to server error', 500);
}
?>
