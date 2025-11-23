<?php
/**
 * CLINIK Update Appointment API
 * Update appointment status or details endpoint
 */

require_once '../../config/database.php';
require_once '../../includes/cors.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Only allow PUT requests
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    sendError('Method not allowed', 405);
}

// Check authentication
$user = Auth::checkAuth();

// Get JSON input
$data = getJsonInput();

// Validate required fields
validateRequired($data, ['appointment_id']);

// Sanitize inputs
$appointment_id = (int)$data['appointment_id'];
$status = sanitizeInput($data['status'] ?? null);
$appointment_date = sanitizeInput($data['appointment_date'] ?? null);
$notes = sanitizeInput($data['notes'] ?? null);

try {
    // Get database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    // Check if appointment exists
    $checkSql = "SELECT appointment_id FROM appointments WHERE appointment_id = :appointment_id";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindParam(':appointment_id', $appointment_id);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        sendError('Appointment not found', 404);
    }
    
    // Build update query dynamically
    $updates = [];
    $params = [':appointment_id' => $appointment_id];
    
    if ($status !== null) {
        $updates[] = "status = :status";
        $params[':status'] = $status;
    }
    
    if ($appointment_date !== null) {
        $updates[] = "appointment_date = :appointment_date";
        $params[':appointment_date'] = $appointment_date;
    }
    
    if ($notes !== null) {
        $updates[] = "notes = :notes";
        $params[':notes'] = $notes;
    }
    
    if (empty($updates)) {
        sendError('No fields to update', 400);
    }
    
    $updates[] = "updated_at = NOW()";
    
    $sql = "UPDATE appointments SET " . implode(', ', $updates) . " WHERE appointment_id = :appointment_id";
    
    $stmt = $conn->prepare($sql);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    
    logMessage("Appointment updated (ID: $appointment_id) by user: " . $user['username'], 'INFO');
    
    sendSuccess('Appointment updated successfully', [
        'appointment_id' => $appointment_id
    ]);
    
} catch (PDOException $e) {
    logMessage("Appointment update error: " . $e->getMessage(), 'ERROR');
    sendError('Failed to update appointment', 500);
}
?>
