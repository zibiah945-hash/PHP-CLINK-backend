<?php
/**
 * CLINIK Backend API
 * Main entry point
 */

require_once 'includes/cors.php';

header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'message' => 'CLINIK Backend API',
    'version' => '1.0.0',
    'endpoints' => [
        'auth' => [
            'POST /api/auth/login.php' => 'User login',
            'POST /api/auth/register.php' => 'User registration',
            'POST /api/auth/logout.php' => 'User logout',
            'GET /api/auth/check-session.php' => 'Check session validity'
        ],
        'patients' => [
            'POST /api/patients/create.php' => 'Create new patient',
            'GET /api/patients/read.php?id={id}' => 'Get patient details',
            'GET /api/patients/list.php' => 'List all patients',
            'PUT /api/patients/update.php' => 'Update patient',
            'GET /api/patients/search.php?q={query}' => 'Search patients'
        ],
        'visits' => [
            'POST /api/visits/create.php' => 'Record new visit',
            'GET /api/visits/history.php?patient_id={id}' => 'Get visit history'
        ],
        'appointments' => [
            'POST /api/appointments/create.php' => 'Schedule appointment',
            'GET /api/appointments/list.php' => 'List appointments',
            'PUT /api/appointments/update.php' => 'Update appointment'
        ]
    ],
    'documentation' => 'See README.md for detailed API documentation'
]);
?>
