<?php
/**
 * CLINIK Read Patient API
 * Get single patient details endpoint
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

// Get patient ID from query parameter
$patient_id = $_GET['id'] ?? null;

if (!$patient_id) {
    sendError('Patient ID is required', 400);
}

try {
    // Get database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    // Get patient details
    $sql = "SELECT 
                p.*,
                u.full_name as created_by_name,
                (SELECT COUNT(*) FROM visits WHERE patient_id = p.patient_id) as total_visits,
                (SELECT MAX(visit_date) FROM visits WHERE patient_id = p.patient_id) as last_visit_date
            FROM patients p
            LEFT JOIN users u ON p.created_by = u.user_id
            WHERE p.patient_id = :patient_id AND p.is_active = 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        sendError('Patient not found', 404);
    }
    
    $patient = $stmt->fetch();
    
    sendSuccess('Patient retrieved successfully', ['patient' => $patient]);
    
} catch (PDOException $e) {
    logMessage("Patient read error: " . $e->getMessage(), 'ERROR');
    sendError('Failed to retrieve patient', 500);
}
?>
