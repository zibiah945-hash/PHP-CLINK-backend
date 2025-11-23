<?php
/**
 * CLINIK Create Visit API
 * Record new patient visit endpoint
 */

require_once '../../config/database.php';
require_once '../../includes/cors.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

// Check authentication
$user = Auth::checkAuth();

// Get JSON input
$data = getJsonInput();

// Validate required fields
validateRequired($data, ['patient_id', 'diagnosis']);

// Sanitize inputs
$patient_id = (int)$data['patient_id'];
$diagnosis = sanitizeInput($data['diagnosis']);
$prescription = sanitizeInput($data['prescription'] ?? '');
$notes = sanitizeInput($data['notes'] ?? '');
$symptoms = sanitizeInput($data['symptoms'] ?? '');
$visit_date = sanitizeInput($data['visit_date'] ?? date('Y-m-d'));
$visit_time = sanitizeInput($data['visit_time'] ?? date('H:i:s'));
$height = isset($data['height']) ? (float)$data['height'] : null;
$weight = isset($data['weight']) ? (float)$data['weight'] : null;
$blood_pressure = sanitizeInput($data['blood_pressure'] ?? null);
$temperature = isset($data['temperature']) ? (float)$data['temperature'] : null;
$pulse = isset($data['pulse']) ? (int)$data['pulse'] : null;

try {
    // Get database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    // Verify patient exists
    $checkSql = "SELECT patient_id, first_name, last_name FROM patients WHERE patient_id = :patient_id AND is_active = 1";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindParam(':patient_id', $patient_id);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        sendError('Patient not found', 404);
    }
    
    $patient = $checkStmt->fetch();
    
    // Insert visit
    $sql = "INSERT INTO visits (
        patient_id, visit_date, visit_time, height, weight, blood_pressure, 
        temperature, pulse, symptoms, diagnosis, prescription, notes, created_by
    ) VALUES (
        :patient_id, :visit_date, :visit_time, :height, :weight, :blood_pressure,
        :temperature, :pulse, :symptoms, :diagnosis, :prescription, :notes, :created_by
    )";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->bindParam(':visit_date', $visit_date);
    $stmt->bindParam(':visit_time', $visit_time);
    $stmt->bindParam(':height', $height);
    $stmt->bindParam(':weight', $weight);
    $stmt->bindParam(':blood_pressure', $blood_pressure);
    $stmt->bindParam(':temperature', $temperature);
    $stmt->bindParam(':pulse', $pulse);
    $stmt->bindParam(':symptoms', $symptoms);
    $stmt->bindParam(':diagnosis', $diagnosis);
    $stmt->bindParam(':prescription', $prescription);
    $stmt->bindParam(':notes', $notes);
    $stmt->bindParam(':created_by', $user['user_id']);
    
    $stmt->execute();
    $visit_id = $conn->lastInsertId();
    
    logMessage("Visit created for patient: {$patient['first_name']} {$patient['last_name']} (ID: $visit_id) by user: " . $user['username'], 'INFO');
    
    sendSuccess('Visit recorded successfully', [
        'visit_id' => $visit_id
    ], 201);
    
} catch (PDOException $e) {
    logMessage("Visit creation error: " . $e->getMessage(), 'ERROR');
    sendError('Failed to record visit', 500);
}
?>
