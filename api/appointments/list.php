<?php
/**
 * CLINIK List Appointments API
 * Get all appointments endpoint
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

// Get filter parameters
$status = $_GET['status'] ?? null;
$date_from = $_GET['date_from'] ?? null;
$date_to = $_GET['date_to'] ?? null;

try {
    // Get database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    // Build query
    $sql = "SELECT 
                a.*,
                p.first_name,
                p.last_name,
                p.phone as contact_number,
                u.full_name as created_by_name
            FROM appointments a
            INNER JOIN patients p ON a.patient_id = p.patient_id
            LEFT JOIN users u ON a.created_by = u.user_id
            WHERE 1=1";
    
    $params = [];
    
    if ($status) {
        $sql .= " AND a.status = :status";
        $params[':status'] = $status;
    }
    
    if ($date_from) {
        $sql .= " AND DATE(a.appointment_date) >= :date_from";
        $params[':date_from'] = $date_from;
    }
    
    if ($date_to) {
        $sql .= " AND DATE(a.appointment_date) <= :date_to";
        $params[':date_to'] = $date_to;
    }
    
    $sql .= " ORDER BY a.appointment_date ASC";
    
    $stmt = $conn->prepare($sql);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $appointments = $stmt->fetchAll();
    
    sendSuccess('Appointments retrieved successfully', [
        'appointments' => $appointments,
        'count' => count($appointments)
    ]);
    
} catch (PDOException $e) {
    logMessage("Appointments list error: " . $e->getMessage(), 'ERROR');
    sendError('Failed to retrieve appointments', 500);
}
?>
