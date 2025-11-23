<?php
/**
 * CLINIK Update Patient API
 * Update patient information endpoint
 */

require_once '../../config/database.php';
require_once '../../includes/cors.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Only allow PUT requests
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    sendError('Method not allowed', 405);
}

// Check authentication
$user = Auth::checkAuth();

// Get JSON input
$data = getJsonInput();

// Validate required fields
validateRequired($data, ['patient_id', 'first_name', 'last_name', 'date_of_birth', 'gender']);

// Sanitize inputs
$patient_id = (int)$data['patient_id'];
$first_name = sanitizeInput($data['first_name']);
$last_name = sanitizeInput($data['last_name']);
$date_of_birth = sanitizeInput($data['date_of_birth']);
$gender = sanitizeInput($data['gender']);
$phone = sanitizeInput($data['phone'] ?? $data['contact_number'] ?? '');
$email = sanitizeInput($data['email'] ?? '');
$address = sanitizeInput($data['address'] ?? '');
$emergency_contact_name = sanitizeInput($data['emergency_contact'] ?? $data['emergency_contact_name'] ?? '');
$emergency_contact_phone = sanitizeInput($data['emergency_contact_phone'] ?? '');
$blood_type = sanitizeInput($data['blood_type'] ?? null);
$allergies = sanitizeInput($data['allergies'] ?? null);
$medical_history = sanitizeInput($data['medical_history'] ?? null);

try {
    // Get database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    // Check if patient exists
    $checkSql = "SELECT patient_id FROM patients WHERE patient_id = :patient_id AND is_active = 1";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindParam(':patient_id', $patient_id);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        sendError('Patient not found', 404);
    }
    
    // Update patient
    $sql = "UPDATE patients SET
        first_name = :first_name,
        last_name = :last_name,
        date_of_birth = :date_of_birth,
        gender = :gender,
        phone = :phone,
        email = :email,
        address = :address,
        emergency_contact_name = :emergency_contact_name,
        emergency_contact_phone = :emergency_contact_phone,
        blood_type = :blood_type,
        allergies = :allergies,
        medical_history = :medical_history
    WHERE patient_id = :patient_id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':first_name', $first_name);
    $stmt->bindParam(':last_name', $last_name);
    $stmt->bindParam(':date_of_birth', $date_of_birth);
    $stmt->bindParam(':gender', $gender);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':emergency_contact_name', $emergency_contact_name);
    $stmt->bindParam(':emergency_contact_phone', $emergency_contact_phone);
    $stmt->bindParam(':blood_type', $blood_type);
    $stmt->bindParam(':allergies', $allergies);
    $stmt->bindParam(':medical_history', $medical_history);
    $stmt->bindParam(':patient_id', $patient_id);
    
    $stmt->execute();
    
    logMessage("Patient updated: $first_name $last_name (ID: $patient_id) by user: " . $user['username'], 'INFO');
    
    sendSuccess('Patient updated successfully', [
        'patient_id' => $patient_id
    ]);
    
} catch (PDOException $e) {
    logMessage("Patient update error: " . $e->getMessage(), 'ERROR');
    sendError('Failed to update patient', 500);
}
?>
