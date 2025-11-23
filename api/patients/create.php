<?php
/**
 * CLINIK Create Patient API
 * Register new patient endpoint
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
validateRequired($data, ['first_name', 'last_name', 'age', 'gender']);

// Sanitize inputs
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
    
    // Begin transaction
    $conn->beginTransaction();
    
    // Generate patient number
    $patient_count = $conn->query("SELECT COUNT(*) FROM patients WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    $patient_number = 'CLIN-' . date('Ymd') . '-' . str_pad($patient_count + 1, 4, '0', STR_PAD_LEFT);
    
    // Insert patient
    $sql = "INSERT INTO patients (
        patient_number, first_name, last_name, date_of_birth, gender, phone, email, address, 
        emergency_contact_name, emergency_contact_phone, blood_type,
        allergies, medical_history, created_by
    ) VALUES (
        :patient_number, :first_name, :last_name, :date_of_birth, :gender, :phone, :email, :address,
        :emergency_contact_name, :emergency_contact_phone, :blood_type,
        :allergies, :medical_history, :created_by
    )";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':patient_number', $patient_number);
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
    $stmt->bindParam(':created_by', $user['user_id']);
    
    $stmt->execute();
    $patient_id = $conn->lastInsertId();
    
    // If diagnosis and prescription provided, create initial visit
    if (!empty($data['diagnosis']) || !empty($data['prescription'])) {
        $diagnosis = sanitizeInput($data['diagnosis'] ?? '');
        $prescription = sanitizeInput($data['prescription'] ?? '');
        $notes = sanitizeInput($data['notes'] ?? '');
        $visit_date = date('Y-m-d');
        $visit_time = date('H:i:s');
        
        $visitSql = "INSERT INTO visits (
            patient_id, visit_date, visit_time, diagnosis, prescription, notes, created_by
        ) VALUES (
            :patient_id, :visit_date, :visit_time, :diagnosis, :prescription, :notes, :created_by
        )";
        
        $visitStmt = $conn->prepare($visitSql);
        $visitStmt->bindParam(':patient_id', $patient_id);
        $visitStmt->bindParam(':visit_date', $visit_date);
        $visitStmt->bindParam(':visit_time', $visit_time);
        $visitStmt->bindParam(':diagnosis', $diagnosis);
        $visitStmt->bindParam(':prescription', $prescription);
        $visitStmt->bindParam(':notes', $notes);
        $visitStmt->bindParam(':created_by', $user['user_id']);
        $visitStmt->execute();
    }
    
    // Commit transaction
    $conn->commit();
    
    logMessage("Patient created: $first_name $last_name (ID: $patient_id) by user: " . $user['username'], 'INFO');
    
    sendSuccess('Patient registered successfully', [
        'patient_id' => $patient_id,
        'first_name' => $first_name,
        'last_name' => $last_name
    ], 201);
    
} catch (PDOException $e) {
    $conn->rollBack();
    logMessage("Patient creation error: " . $e->getMessage(), 'ERROR');
    sendError('Failed to create patient', 500);
}
?>
