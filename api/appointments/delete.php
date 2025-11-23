<?php
/**
 * CLINIK Delete Appointment API
 * Delete or cancel appointment endpoint
 */

require_once '../../config/database.php';
require_once '../../includes/cors.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Only allow DELETE requests
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    sendError('Method not allowed', 405);
}

// Check authentication
$user = Auth::checkAuth();

// Get JSON input
$data = getJsonInput();

// Validate required fields
validateRequired($data, ['appointment_id']);

$appointment_id = (int)$data['appointment_id'];

try {
    // Get database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    // Check if appointment exists
    $checkSql = "SELECT appointment_id, patient_id FROM appointments WHERE appointment_id = :appointment_id";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindParam(':appointment_id', $appointment_id);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        sendError('Appointment not found', 404);
    }
    
    // Delete appointment
    $sql = "DELETE FROM appointments WHERE appointment_id = :appointment_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':appointment_id', $appointment_id);
    $stmt->execute();
    
    logMessage("Appointment deleted (ID: $appointment_id) by user: " . $user['username'], 'INFO');
    
    sendSuccess('Appointment deleted successfully', [
        'appointment_id' => $appointment_id
    ]);
    
} catch (PDOException $e) {
    logMessage("Appointment deletion error: " . $e->getMessage(), 'ERROR');
    sendError('Failed to delete appointment', 500);
}
?>
