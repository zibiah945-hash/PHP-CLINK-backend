<?php
/**
 * CLINIK Create Appointment API
 * Schedule new appointment endpoint
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
validateRequired($data, ['patient_id', 'appointment_date', 'appointment_time', 'purpose']);

// Sanitize inputs
$patient_id = (int)$data['patient_id'];
$appointment_date = sanitizeInput($data['appointment_date']);
$appointment_time = sanitizeInput($data['appointment_time']);
$purpose = sanitizeInput($data['purpose']);
$status = sanitizeInput($data['status'] ?? 'Scheduled');
$notes = sanitizeInput($data['notes'] ?? '');

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
    
    // Insert appointment
    $sql = "INSERT INTO appointments (
        patient_id, appointment_date, appointment_time, purpose, status, notes, created_by
    ) VALUES (
        :patient_id, :appointment_date, :appointment_time, :purpose, :status, :notes, :created_by
    )";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->bindParam(':appointment_date', $appointment_date);
    $stmt->bindParam(':appointment_time', $appointment_time);
    $stmt->bindParam(':purpose', $purpose);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':notes', $notes);
    $stmt->bindParam(':created_by', $user['user_id']);
    
    $stmt->execute();
    $appointment_id = $conn->lastInsertId();
    
    logMessage("Appointment created for patient: {$patient['first_name']} {$patient['last_name']} (ID: $appointment_id) by user: " . $user['username'], 'INFO');
    
    sendSuccess('Appointment scheduled successfully', [
        'appointment_id' => $appointment_id
    ], 201);
    
} catch (PDOException $e) {
    logMessage("Appointment creation error: " . $e->getMessage(), 'ERROR');
    sendError('Failed to schedule appointment', 500);
}
?>
