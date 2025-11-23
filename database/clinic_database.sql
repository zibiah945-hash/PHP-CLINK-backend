-- ============================================================================
-- CLINIK Database Creation Script
-- Fixed version for MySQL compatibility
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS clinic_db;
CREATE DATABASE clinic_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE clinic_db;

-- ============================================================================
-- USERS TABLE (Admin and Staff)
-- ============================================================================
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'staff') DEFAULT 'staff' NOT NULL,
    is_active BOOLEAN DEFAULT TRUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Constraints
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- PATIENTS TABLE
-- ============================================================================
CREATE TABLE patients (
    patient_id INT PRIMARY KEY AUTO_INCREMENT,
    patient_number VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(20),
    blood_type ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'Unknown'),
    allergies TEXT,
    medical_history TEXT,
    is_active BOOLEAN DEFAULT TRUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Constraints
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE RESTRICT,
    INDEX idx_patient_number (patient_number),
    INDEX idx_full_name (first_name, last_name),
    INDEX idx_phone (phone),
    INDEX idx_dob (date_of_birth)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- VISITS TABLE (Patient visits with diagnosis and prescription)
-- ============================================================================
CREATE TABLE visits (
    visit_id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    visit_date DATE NOT NULL,
    visit_time TIME NOT NULL,
    height DECIMAL(5,2) COMMENT 'in cm',
    weight DECIMAL(5,2) COMMENT 'in kg',
    blood_pressure VARCHAR(20),
    temperature DECIMAL(4,2) COMMENT 'in Celsius',
    pulse INT,
    symptoms TEXT,
    diagnosis TEXT NOT NULL,
    prescription TEXT,
    notes TEXT,
    follow_up_date DATE,
    status ENUM('Completed', 'Scheduled', 'Cancelled') DEFAULT 'Completed' NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Constraints
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE RESTRICT,
    INDEX idx_visit_date (visit_date),
    INDEX idx_patient_visit (patient_id, visit_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- APPOINTMENTS TABLE
-- ============================================================================
CREATE TABLE appointments (
    appointment_id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    purpose VARCHAR(255) NOT NULL,
    status ENUM('Scheduled', 'Confirmed', 'Completed', 'Cancelled', 'No-Show') DEFAULT 'Scheduled' NOT NULL,
    notes TEXT,
    reminder_sent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Constraints
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE RESTRICT,
    INDEX idx_appointment_date (appointment_date),
    INDEX idx_status (status),
    INDEX idx_patient_appointment (patient_id, appointment_date),
    
    -- Prevent duplicate appointments for same patient at same time
    UNIQUE KEY unique_patient_appointment (patient_id, appointment_date, appointment_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- CLINIC_SETTINGS TABLE
-- ============================================================================
CREATE TABLE clinic_settings (
    setting_id INT PRIMARY KEY AUTO_INCREMENT,
    clinic_name VARCHAR(100) NOT NULL DEFAULT 'CLINIK',
    clinic_address TEXT NOT NULL,
    clinic_phone VARCHAR(20) NOT NULL,
    clinic_email VARCHAR(100) NOT NULL,
    working_hours_start TIME NOT NULL DEFAULT '08:00:00',
    working_hours_end TIME NOT NULL DEFAULT '17:00:00',
    max_patients_per_day INT NOT NULL DEFAULT 50,
    appointment_duration INT NOT NULL DEFAULT 30 COMMENT 'in minutes',
    auto_backup BOOLEAN DEFAULT TRUE,
    backup_frequency ENUM('daily', 'weekly', 'monthly') DEFAULT 'daily',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT,
    
    -- Constraints
    FOREIGN KEY (updated_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- AUDIT_LOGS TABLE (For tracking all changes)
-- ============================================================================
CREATE TABLE audit_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL COMMENT 'CREATE, UPDATE, DELETE, LOGIN, etc.',
    table_name VARCHAR(50) NOT NULL,
    record_id INT NOT NULL,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Constraints
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE RESTRICT,
    INDEX idx_action (action),
    INDEX idx_table (table_name),
    INDEX idx_created_at (created_at),
    INDEX idx_user_action (user_id, action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- BACKUP_METADATA TABLE
-- ============================================================================
CREATE TABLE backup_metadata (
    backup_id INT PRIMARY KEY AUTO_INCREMENT,
    backup_name VARCHAR(255) NOT NULL,
    backup_size BIGINT NOT NULL COMMENT 'in bytes',
    backup_type ENUM('auto', 'manual') NOT NULL,
    backup_path VARCHAR(500) NOT NULL,
    status ENUM('success', 'failed', 'in_progress') DEFAULT 'success',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    
    -- Constraints
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_created_at (created_at),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- DEFAULT DATA INSERTION
-- ============================================================================

-- Insert default admin user (password: '1234' hashed)
INSERT INTO users (username, password_hash, email, full_name, role, is_active, created_by) 
VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@clinic.local', 'System Administrator', 'admin', TRUE, 1);

-- Insert default clinic settings
INSERT INTO clinic_settings (clinic_name, clinic_address, clinic_phone, clinic_email, working_hours_start, working_hours_end, max_patients_per_day, appointment_duration) 
VALUES
('CLINIK', '123 Medical Street, Healthcare City', '+1-555-0100', 'info@clinic.local', '08:00:00', '17:00:00', 50, 30);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- FINAL VERIFICATION QUERY
-- ============================================================================
SELECT 'Database created successfully!' AS status,
       (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'clinic_db') AS tables_created,
       (SELECT COUNT(*) FROM users) AS users_count,
       (SELECT COUNT(*) FROM clinic_settings) AS settings_count;
