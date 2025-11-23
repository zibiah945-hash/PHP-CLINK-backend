<?php
/**
 * CLINIK Delete Patient API
 * Soft delete patient endpoint
 */

require_once '../../config/database.php';
require_once '../../includes/cors.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Only allow DELETE requests
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    sendError('Method not allowed', 405);
}

// Check authentication (admin only for delete)
$user = Auth::requireAdmin();

// Get JSON input
$data = getJsonInput();

// Validate required fields
validateRequired($data, ['patient_id']);

$patient_id = (int)$data['patient_id'];

try {
    // Get database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    // Check if patient exists
    $checkSql = "SELECT patient_id, first_name, last_name FROM patients WHERE patient_id = :patient_id AND is_active = 1";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindParam(':patient_id', $patient_id);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        sendError('Patient not found', 404);
    }
    
    $patient = $checkStmt->fetch();
    
    // Soft delete (set is_active to FALSE)
    $sql = "UPDATE patients SET is_active = 0 WHERE patient_id = :patient_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->execute();
    
    logMessage("Patient deleted: {$patient['first_name']} {$patient['last_name']} (ID: $patient_id) by user: " . $user['username'], 'INFO');
    
    sendSuccess('Patient deleted successfully', [
        'patient_id' => $patient_id
    ]);
    
} catch (PDOException $e) {
    logMessage("Patient deletion error: " . $e->getMessage(), 'ERROR');
    sendError('Failed to delete patient', 500);
}
?>
