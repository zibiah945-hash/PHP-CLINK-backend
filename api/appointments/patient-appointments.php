<?php
/**
 * CLINIK Patient Appointments API
 * Get specific patient's appointments endpoint
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

// Get patient ID
$patient_id = $_GET['patient_id'] ?? null;

if (!$patient_id) {
    sendError('Patient ID is required', 400);
}

try {
    // Get database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    // Get patient's appointments
    $sql = "SELECT 
                a.*,
                u.full_name as created_by_name
            FROM appointments a
            LEFT JOIN users u ON a.created_by = u.user_id
            WHERE a.patient_id = :patient_id
            ORDER BY a.appointment_date DESC, a.appointment_time DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->execute();
    
    $appointments = $stmt->fetchAll();
    
    sendSuccess('Patient appointments retrieved successfully', [
        'appointments' => $appointments,
        'count' => count($appointments)
    ]);
    
} catch (PDOException $e) {
    logMessage("Patient appointments error: " . $e->getMessage(), 'ERROR');
    sendError('Failed to retrieve patient appointments', 500);
}
?>
