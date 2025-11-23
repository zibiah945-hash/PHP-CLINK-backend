<?php
/**
 * CLINIK Registration API
 * User registration endpoint
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
validateRequired($data, ['username', 'password', 'email', 'full_name']);

// Sanitize inputs
$username = sanitizeInput($data['username']);
$password = $data['password'];
$email = sanitizeInput($data['email']);
$full_name = sanitizeInput($data['full_name']);
$role = sanitizeInput($data['role'] ?? 'staff');

// Validate email
if (!validateEmail($email)) {
    sendError('Invalid email address', 400);
}

// Validate username (alphanumeric only)
if (!preg_match('/^[A-Za-z0-9_]+$/', $username)) {
    sendError('Username must contain only letters, numbers and underscores', 400);
}

// Validate password length
if (strlen($password) < 4) {
    sendError('Password must be at least 4 characters', 400);
}

try {
    // Get database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    // Check if username already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        sendError('Username already exists', 400);
    }
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        sendError('Email already registered', 400);
    }
    
    // Hash password
    $password_hash = Auth::hashPassword($password);
    
    // Insert new user
    $sql = "INSERT INTO users (username, password_hash, email, full_name, role, is_active, created_by) 
            VALUES (:username, :password_hash, :email, :full_name, :role, 1, 1)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password_hash', $password_hash);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':full_name', $full_name);
    $stmt->bindParam(':role', $role);
    
    if ($stmt->execute()) {
        $user_id = $conn->lastInsertId();
        
        logMessage("New user registered: $username (ID: $user_id)", 'INFO');
        
        sendSuccess('Account created successfully', [
            'user_id' => $user_id,
            'username' => $username,
            'email' => $email,
            'full_name' => $full_name,
            'role' => $role
        ], 201);
    } else {
        throw new Exception('Failed to create user');
    }
    
} catch (PDOException $e) {
    logMessage("Registration error: " . $e->getMessage(), 'ERROR');
    sendError('Registration failed due to server error', 500);
}
?>
