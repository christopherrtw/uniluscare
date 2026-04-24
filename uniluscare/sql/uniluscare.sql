-- =====================================================
-- UnilusCare Hospital Management System Database
-- =====================================================
-- Run this in phpMyAdmin or MySQL to set up the database
-- =====================================================

CREATE DATABASE IF NOT EXISTS uniluscare CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE uniluscare;

-- =====================================================
-- ADMIN TABLE (only role with a password)
-- =====================================================
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL, -- stored as password_hash
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin: username=admin, password=Admin@123
INSERT INTO admins (username, password, full_name, email) VALUES
('admin', '$2y$10$w8aRcVOpNY0MoqlHfHzGse7s8hFcgFxV2w9I1BDr3iPbI/PHzhT12', 'System Administrator', 'admin@uniluscare.zm');

-- =====================================================
-- PATIENTS TABLE
-- =====================================================
CREATE TABLE patients (
    patient_id VARCHAR(20) PRIMARY KEY, -- e.g. P000001
    id_number VARCHAR(50) NOT NULL, -- NRC / Passport
    id_type VARCHAR(20) DEFAULT 'NRC',
    suffix VARCHAR(10),
    full_name VARCHAR(150) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender VARCHAR(10),
    phone VARCHAR(30) NOT NULL,
    email VARCHAR(150),
    address TEXT,
    employer VARCHAR(150),
    insurance_company VARCHAR(150),
    insurance_number VARCHAR(100),
    scheme_type VARCHAR(100),
    reason_visit VARCHAR(100),
    created_by INT, -- receptionist_id who registered
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- STAFF TABLES (no passwords — only IDs)
-- =====================================================
CREATE TABLE doctors (
    doctor_id VARCHAR(20) PRIMARY KEY, -- e.g. D0001
    full_name VARCHAR(150) NOT NULL,
    department VARCHAR(100) NOT NULL,
    specialization VARCHAR(150),
    phone VARCHAR(30),
    email VARCHAR(150),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE receptionists (
    receptionist_id VARCHAR(20) PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(30),
    email VARCHAR(150),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE nurses (
    nurse_id VARCHAR(20) PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    ward VARCHAR(100),
    phone VARCHAR(30),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE pharmacists (
    pharmacist_id VARCHAR(20) PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(30),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE lab_technicians (
    lab_tech_id VARCHAR(20) PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(30),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE radiologists (
    radiologist_id VARCHAR(20) PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(30),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE inventory_managers (
    inv_manager_id VARCHAR(20) PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(30),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE triage_officers (
    triage_id VARCHAR(20) PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(30),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- APPOINTMENTS
-- =====================================================
CREATE TABLE appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id VARCHAR(20) NOT NULL,
    doctor_id VARCHAR(20),
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    reason VARCHAR(255),
    type VARCHAR(20) DEFAULT 'physical', -- physical | virtual
    status VARCHAR(20) DEFAULT 'pending', -- pending | confirmed | completed | cancelled
    room_id VARCHAR(50), -- for virtual
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =====================================================
-- VITALS
-- =====================================================
CREATE TABLE vitals (
    vital_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id VARCHAR(20) NOT NULL,
    recorded_by VARCHAR(20), -- nurse or doctor id
    bp_systolic INT,
    bp_diastolic INT,
    heart_rate INT,
    temperature DECIMAL(4,1),
    respiratory_rate INT,
    oxygen_saturation INT,
    weight DECIMAL(5,2),
    height DECIMAL(5,2),
    notes TEXT,
    recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- PRESCRIPTIONS
-- =====================================================
CREATE TABLE prescriptions (
    prescription_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id VARCHAR(20) NOT NULL,
    doctor_id VARCHAR(20),
    diagnosis TEXT,
    icd10_code VARCHAR(20),
    notes TEXT,
    status VARCHAR(20) DEFAULT 'active', -- active | dispensed | cancelled
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE prescription_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    prescription_id INT NOT NULL,
    medicine_name VARCHAR(150) NOT NULL,
    dosage VARCHAR(100),
    frequency VARCHAR(100),
    duration VARCHAR(100),
    instructions TEXT,
    dispensed TINYINT(1) DEFAULT 0,
    FOREIGN KEY (prescription_id) REFERENCES prescriptions(prescription_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- MEDICAL RECORDS
-- =====================================================
CREATE TABLE medical_records (
    record_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id VARCHAR(20) NOT NULL,
    doctor_id VARCHAR(20),
    visit_date DATE,
    diagnosis TEXT,
    treatment TEXT,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- LAB TESTS
-- =====================================================
CREATE TABLE lab_tests (
    test_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id VARCHAR(20) NOT NULL,
    doctor_id VARCHAR(20),
    lab_tech_id VARCHAR(20),
    test_name VARCHAR(150) NOT NULL,
    test_type VARCHAR(100),
    status VARCHAR(30) DEFAULT 'requested', -- requested | sample_collected | in_progress | completed
    results TEXT,
    result_file VARCHAR(255),
    notes TEXT,
    requested_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- IMAGING / RADIOLOGY
-- =====================================================
CREATE TABLE imaging_reports (
    imaging_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id VARCHAR(20) NOT NULL,
    doctor_id VARCHAR(20), -- ordering doctor
    radiologist_id VARCHAR(20),
    image_type VARCHAR(50), -- X-ray | MRI | CT | Ultrasound
    body_part VARCHAR(100),
    findings TEXT,
    ai_analysis TEXT,
    image_file VARCHAR(255),
    status VARCHAR(30) DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- PHARMACY INVENTORY
-- =====================================================
CREATE TABLE medicines (
    medicine_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    category VARCHAR(100),
    manufacturer VARCHAR(150),
    batch_number VARCHAR(100),
    stock_quantity INT DEFAULT 0,
    reorder_level INT DEFAULT 10,
    unit_price DECIMAL(10,2),
    expiry_date DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- INVENTORY (supplies/equipment)
-- =====================================================
CREATE TABLE inventory_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    category VARCHAR(100),
    quantity INT DEFAULT 0,
    reorder_level INT DEFAULT 5,
    unit_price DECIMAL(10,2),
    supplier VARCHAR(150),
    expiry_date DATE NULL,
    last_restocked DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE purchase_orders (
    po_id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT,
    quantity INT,
    supplier VARCHAR(150),
    status VARCHAR(30) DEFAULT 'pending',
    created_by VARCHAR(20),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES inventory_items(item_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- BILLING
-- =====================================================
CREATE TABLE invoices (
    invoice_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id VARCHAR(20) NOT NULL,
    description TEXT,
    amount DECIMAL(10,2) NOT NULL,
    tax DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    payment_status VARCHAR(30) DEFAULT 'unpaid', -- unpaid | paid | partial | insurance_pending
    payment_method VARCHAR(30), -- cash | card | insurance
    insurance_claim_status VARCHAR(30),
    created_by VARCHAR(20),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- NOTIFICATIONS
-- =====================================================
CREATE TABLE notifications (
    notif_id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_id VARCHAR(20) NOT NULL, -- patient/doctor/etc id
    recipient_role VARCHAR(30) NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT,
    type VARCHAR(30), -- lab | medication | appointment | system
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- ER / TRIAGE
-- =====================================================
CREATE TABLE triage_cases (
    case_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id VARCHAR(20),
    patient_name VARCHAR(150), -- for rapid reg if not yet registered
    priority VARCHAR(10), -- red | yellow | green
    chief_complaint TEXT,
    triage_officer_id VARCHAR(20),
    assigned_doctor_id VARCHAR(20),
    status VARCHAR(30) DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- NURSING NOTES & MEDICATION ADMINISTRATION
-- =====================================================
CREATE TABLE nursing_notes (
    note_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id VARCHAR(20) NOT NULL,
    nurse_id VARCHAR(20),
    note TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE medication_administration (
    med_admin_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id VARCHAR(20) NOT NULL,
    nurse_id VARCHAR(20),
    medicine_name VARCHAR(150),
    dosage VARCHAR(100),
    administered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- FEEDBACK (patient engagement)
-- =====================================================
CREATE TABLE patient_feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id VARCHAR(20) NOT NULL,
    rating INT,
    comments TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- SAMPLE DATA
-- =====================================================

-- Sample doctors (login with these IDs)
INSERT INTO doctors (doctor_id, full_name, department, specialization, phone, email) VALUES
('D0001', 'Dr Dzimadzi', 'General Medicine', 'Internal Medicine', '+260 977 000001', 'dzimadzi@uniluscare.zm'),
('D0002', 'Dr Banda', 'Cardiology', 'Cardiologist', '+260 977 000002', 'banda@uniluscare.zm'),
('D0003', 'Dr Mwansa', 'Pediatrics', 'Pediatrician', '+260 977 000003', 'mwansa@uniluscare.zm'),
('D0004', 'Dr Phiri', 'Surgery', 'General Surgeon', '+260 977 000004', 'phiri@uniluscare.zm'),
('D0005', 'Dr Chanda', 'Obstetrics & Gynecology', 'OB/GYN', '+260 977 000005', 'chanda@uniluscare.zm');

-- Sample staff
INSERT INTO receptionists (receptionist_id, full_name, phone) VALUES
('R0001', 'Mary Tembo', '+260 966 111111'),
('R0002', 'Grace Sakala', '+260 966 111112');

INSERT INTO nurses (nurse_id, full_name, ward, phone) VALUES
('N0001', 'Nurse Zulu', 'General Ward', '+260 966 222222'),
('N0002', 'Nurse Lungu', 'Pediatric Ward', '+260 966 222223');

INSERT INTO pharmacists (pharmacist_id, full_name, phone) VALUES
('PH001', 'Joseph Mulenga', '+260 966 333333');

INSERT INTO lab_technicians (lab_tech_id, full_name, phone) VALUES
('L0001', 'Peter Kabwe', '+260 966 444444');

INSERT INTO radiologists (radiologist_id, full_name, phone) VALUES
('RD001', 'Dr Simba', '+260 966 555555');

INSERT INTO inventory_managers (inv_manager_id, full_name, phone) VALUES
('IM001', 'Susan Nkhoma', '+260 966 666666');

INSERT INTO triage_officers (triage_id, full_name, phone) VALUES
('T0001', 'Moses Chileshe', '+260 966 777777');

-- Sample patients (for testing — password not needed, only ID)
INSERT INTO patients (patient_id, id_number, id_type, suffix, full_name, date_of_birth, gender, phone, email, address, reason_visit)
VALUES
('P000001', '123456/78/1', 'NRC', 'Mr', 'John Mubanga', '1985-06-15', 'Male', '+260 977 123456', 'john@email.com', 'Plot 12, Lusaka', 'other'),
('P000002', '987654/21/1', 'NRC', 'Mrs', 'Mary Banda', '1990-03-22', 'Female', '+260 977 654321', 'mary@email.com', 'Plot 45, Kitwe', 'accident at home');

-- Sample medicines
INSERT INTO medicines (name, category, manufacturer, batch_number, stock_quantity, reorder_level, unit_price, expiry_date) VALUES
('Paracetamol 500mg', 'Analgesic', 'GSK', 'BATCH001', 500, 50, 0.50, '2027-06-30'),
('Amoxicillin 250mg', 'Antibiotic', 'Pfizer', 'BATCH002', 200, 30, 1.20, '2026-12-31'),
('Ibuprofen 400mg', 'Analgesic', 'Bayer', 'BATCH003', 5, 20, 0.80, '2027-03-15'),
('Metformin 500mg', 'Antidiabetic', 'Merck', 'BATCH004', 150, 25, 1.50, '2027-08-20'),
('Amlodipine 5mg', 'Antihypertensive', 'Novartis', 'BATCH005', 100, 20, 2.00, '2027-05-10');

-- Sample inventory
INSERT INTO inventory_items (name, category, quantity, reorder_level, unit_price, supplier, expiry_date) VALUES
('Surgical Gloves (Box)', 'Consumables', 300, 50, 15.00, 'MedSupply Zambia', '2028-01-01'),
('Syringes 5ml', 'Consumables', 2000, 200, 0.30, 'MedSupply Zambia', '2028-06-30'),
('Face Masks (Box)', 'PPE', 120, 30, 12.00, 'Zambian Medical Supplies', '2027-12-31'),
('Bandages', 'Consumables', 3, 20, 2.50, 'MedSupply Zambia', NULL),
('Thermometers', 'Equipment', 25, 5, 35.00, 'MedTech Ltd', NULL);

-- Sample appointments
INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, status) VALUES
('P000001', 'D0001', CURDATE(), '10:00:00', 'Routine Checkup', 'confirmed'),
('P000002', 'D0002', CURDATE(), '14:30:00', 'Chest pain follow-up', 'pending');

-- Sample vitals
INSERT INTO vitals (patient_id, bp_systolic, bp_diastolic, heart_rate, temperature, respiratory_rate, oxygen_saturation, weight, height) VALUES
('P000001', 120, 80, 72, 36.7, 16, 98, 70.5, 175),
('P000002', 135, 88, 78, 37.1, 18, 97, 65.0, 165);

-- Sample notifications
INSERT INTO notifications (recipient_id, recipient_role, title, message, type) VALUES
('P000001', 'patient', 'Appointment Confirmed', 'Your appointment with Dr Dzimadzi is confirmed for today at 10:00.', 'appointment'),
('P000001', 'patient', 'Lab Result Ready', 'Your blood test results are available in your records.', 'lab');
