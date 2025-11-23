<?php
/**
 * CLINIK Visit History API
 * Get patient visit history endpoint
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
    
    // Get visit history
    $sql = "SELECT 
                v.*,
                u.full_name as created_by_name
            FROM visits v
            LEFT JOIN users u ON v.created_by = u.user_id
            WHERE v.patient_id = :patient_id
            ORDER BY v.visit_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->execute();
    
    $visits = $stmt->fetchAll();
    
    sendSuccess('Visit history retrieved successfully', [
        'visits' => $visits,
        'count' => count($visits)
    ]);
    
} catch (PDOException $e) {
    logMessage("Visit history error: " . $e->getMessage(), 'ERROR');
    sendError('Failed to retrieve visit history', 500);
}
?>
