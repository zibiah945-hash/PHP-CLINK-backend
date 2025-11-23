<?php
/**
 * CLINIK Upcoming Appointments API
 * Get upcoming appointments endpoint
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

// Get optional limit parameter
$limit = isset($_GET['limit']) ? min(max(1, (int)$_GET['limit']), 100) : 20;

try {
    // Get database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    // Get upcoming appointments
    $sql = "SELECT 
                a.appointment_id,
                a.appointment_date,
                a.appointment_time,
                a.purpose,
                a.status,
                a.notes,
                p.patient_id,
                p.first_name,
                p.last_name,
                p.phone as contact_number,
                u.full_name as created_by_name
            FROM appointments a
            INNER JOIN patients p ON a.patient_id = p.patient_id
            LEFT JOIN users u ON a.created_by = u.user_id
            WHERE a.appointment_date >= CURDATE()
            AND a.status IN ('Scheduled', 'Confirmed')
            ORDER BY a.appointment_date ASC, a.appointment_time ASC
            LIMIT :limit";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    $appointments = $stmt->fetchAll();
    
    sendSuccess('Upcoming appointments retrieved successfully', [
        'appointments' => $appointments,
        'count' => count($appointments)
    ]);
    
} catch (PDOException $e) {
    logMessage("Upcoming appointments error: " . $e->getMessage(), 'ERROR');
    sendError('Failed to retrieve upcoming appointments', 500);
}
?>
