<?php
/**
 * CLINIK Search Patients API
 * Search patients by name or contact endpoint
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

// Get search query
$query = $_GET['q'] ?? '';

if (empty($query)) {
    sendError('Search query is required', 400);
}

$searchTerm = '%' . sanitizeInput($query) . '%';

try {
    // Get database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    // Search patients
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
                p.created_at,
                (SELECT COUNT(*) FROM visits WHERE patient_id = p.patient_id) as total_visits
            FROM patients p
            WHERE p.is_active = 1
            AND (
                p.first_name LIKE :search1
                OR p.last_name LIKE :search2
                OR p.patient_number LIKE :search3
                OR p.phone LIKE :search4
                OR p.email LIKE :search5
                OR CONCAT(p.first_name, ' ', p.last_name) LIKE :search6
            )
            ORDER BY p.created_at DESC
            LIMIT 50";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':search1', $searchTerm);
    $stmt->bindParam(':search2', $searchTerm);
    $stmt->bindParam(':search3', $searchTerm);
    $stmt->bindParam(':search4', $searchTerm);
    $stmt->bindParam(':search5', $searchTerm);
    $stmt->bindParam(':search6', $searchTerm);
    $stmt->execute();
    
    $patients = $stmt->fetchAll();
    
    sendSuccess('Search completed successfully', [
        'patients' => $patients,
        'count' => count($patients)
    ]);
    
} catch (PDOException $e) {
    logMessage("Patient search error: " . $e->getMessage(), 'ERROR');
    sendError('Failed to search patients', 500);
}
?>
