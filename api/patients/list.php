<?php
/**
 * CLINIK List Patients API
 * Get all patients endpoint
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

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? min(max(1, (int)$_GET['limit']), 100) : 50;
$offset = ($page - 1) * $limit;

try {
    // Get database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM patients WHERE is_active = 1";
    $countStmt = $conn->query($countSql);
    $total = $countStmt->fetch()['total'];
    
    // Get patients with pagination
    $sql = "SELECT 
                p.patient_id,
                p.patient_number,
                p.first_name,
                p.last_name,
                p.date_of_birth,
                TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) as age,
                p.gender,
                p.phone,
                p.email,
                p.address,
                p.blood_type,
                p.created_at,
                (SELECT COUNT(*) FROM visits WHERE patient_id = p.patient_id) as total_visits,
                (SELECT MAX(visit_date) FROM visits WHERE patient_id = p.patient_id) as last_visit_date
            FROM patients p
            WHERE p.is_active = 1
            ORDER BY p.created_at DESC
            LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $patients = $stmt->fetchAll();
    
    sendSuccess('Patients retrieved successfully', [
        'patients' => $patients,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => (int)$total,
            'total_pages' => ceil($total / $limit)
        ]
    ]);
    
} catch (PDOException $e) {
    logMessage("Patients list error: " . $e->getMessage(), 'ERROR');
    sendError('Failed to retrieve patients', 500);
}
?>
