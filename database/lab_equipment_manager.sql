-- =====================================================
-- Lab & Equipment Manager - 12 Tables Database Schema
-- For MySQL/MariaDB / phpMyAdmin
-- Project: INS3064 Multimedia Design and Web Development
-- Default sample password for all users: 123456
-- =====================================================

CREATE DATABASE IF NOT EXISTS lab_equipment_manager
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE lab_equipment_manager;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS penalty_payments;
DROP TABLE IF EXISTS damage_reports;
DROP TABLE IF EXISTS maintenance_logs;
DROP TABLE IF EXISTS maintenance_schedules;
DROP TABLE IF EXISTS borrow_records;
DROP TABLE IF EXISTS borrow_requests;
DROP TABLE IF EXISTS equipment;
DROP TABLE IF EXISTS suppliers;
DROP TABLE IF EXISTS equipment_categories;
DROP TABLE IF EXISTS labs;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS departments;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- 1. departments
-- Stores departments/faculties that own users and labs.
-- =====================================================
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_code VARCHAR(30) NOT NULL UNIQUE,
    department_name VARCHAR(120) NOT NULL,
    description TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. users
-- Stores system accounts: admin, student, technician.
-- =====================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_id INT NULL,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'student', 'technician') NOT NULL DEFAULT 'student',
    phone VARCHAR(20),
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_users_department
        FOREIGN KEY (department_id) REFERENCES departments(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. labs
-- Stores lab rooms such as AI Lab, IoT Lab, Multimedia Lab.
-- =====================================================
CREATE TABLE labs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_id INT NULL,
    lab_code VARCHAR(30) NOT NULL UNIQUE,
    lab_name VARCHAR(120) NOT NULL,
    location VARCHAR(150),
    capacity INT NOT NULL DEFAULT 0,
    manager_user_id INT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_labs_department
        FOREIGN KEY (department_id) REFERENCES departments(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT fk_labs_manager
        FOREIGN KEY (manager_user_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT chk_labs_capacity
        CHECK (capacity >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. equipment_categories
-- Stores equipment categories and default maintenance cycles.
-- =====================================================
CREATE TABLE equipment_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(120) NOT NULL UNIQUE,
    description TEXT,
    maintenance_cycle_days INT NULL,
    calibration_cycle_days INT NULL,

    CONSTRAINT chk_category_maintenance_cycle
        CHECK (maintenance_cycle_days IS NULL OR maintenance_cycle_days > 0),

    CONSTRAINT chk_category_calibration_cycle
        CHECK (calibration_cycle_days IS NULL OR calibration_cycle_days > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. suppliers
-- Stores suppliers/vendors of lab equipment.
-- =====================================================
CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_name VARCHAR(150) NOT NULL,
    contact_person VARCHAR(120),
    phone VARCHAR(20),
    email VARCHAR(150),
    address VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. equipment
-- Stores individual equipment/assets.
-- =====================================================
CREATE TABLE equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_code VARCHAR(50) NOT NULL UNIQUE,
    equipment_name VARCHAR(150) NOT NULL,
    category_id INT NOT NULL,
    lab_id INT NOT NULL,
    supplier_id INT NULL,
    serial_number VARCHAR(100) UNIQUE,
    purchase_date DATE,
    purchase_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    status ENUM('available', 'borrowed', 'maintenance', 'damaged', 'retired') NOT NULL DEFAULT 'available',
    total_used_hours DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_equipment_category
        FOREIGN KEY (category_id) REFERENCES equipment_categories(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_equipment_lab
        FOREIGN KEY (lab_id) REFERENCES labs(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_equipment_supplier
        FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT chk_equipment_price
        CHECK (purchase_price >= 0),

    CONSTRAINT chk_equipment_used_hours
        CHECK (total_used_hours >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. borrow_requests
-- Stores equipment borrowing requests from users.
-- =====================================================
CREATE TABLE borrow_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    equipment_id INT NOT NULL,
    purpose TEXT NOT NULL,
    start_time DATETIME NOT NULL,
    expected_return_time DATETIME NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'cancelled', 'completed') NOT NULL DEFAULT 'pending',
    approved_by INT NULL,
    approved_at DATETIME NULL,
    note TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_borrow_requests_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_borrow_requests_equipment
        FOREIGN KEY (equipment_id) REFERENCES equipment(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_borrow_requests_approver
        FOREIGN KEY (approved_by) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT chk_borrow_request_time
        CHECK (expected_return_time > start_time),

    INDEX idx_borrow_requests_user (user_id),
    INDEX idx_borrow_requests_equipment (equipment_id),
    INDEX idx_borrow_requests_status (status),
    INDEX idx_borrow_requests_time (start_time, expected_return_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. borrow_records
-- Stores actual check-out/check-in records.
-- One approved borrow request should create one borrow record.
-- =====================================================
CREATE TABLE borrow_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL UNIQUE,
    checkout_by INT NOT NULL,
    checkout_time DATETIME NOT NULL,
    expected_return_time DATETIME NOT NULL,
    checkin_by INT NULL,
    checkin_time DATETIME NULL,
    condition_out TEXT,
    condition_in TEXT,
    used_hours DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    status ENUM('checked_out', 'returned', 'overdue', 'lost', 'damaged') NOT NULL DEFAULT 'checked_out',
    note TEXT,

    CONSTRAINT fk_borrow_records_request
        FOREIGN KEY (request_id) REFERENCES borrow_requests(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_borrow_records_checkout_by
        FOREIGN KEY (checkout_by) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_borrow_records_checkin_by
        FOREIGN KEY (checkin_by) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT chk_borrow_record_used_hours
        CHECK (used_hours >= 0),

    CONSTRAINT chk_borrow_record_checkin_time
        CHECK (checkin_time IS NULL OR checkin_time >= checkout_time),

    INDEX idx_borrow_records_status (status),
    INDEX idx_borrow_records_checkout_time (checkout_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 9. maintenance_schedules
-- Stores planned maintenance/calibration schedules.
-- =====================================================
CREATE TABLE maintenance_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_id INT NOT NULL,
    maintenance_type ENUM('maintenance', 'calibration') NOT NULL,
    scheduled_date DATE NOT NULL,
    frequency_days INT NULL,
    usage_hour_interval INT NULL,
    status ENUM('planned', 'in_progress', 'completed', 'overdue', 'cancelled') NOT NULL DEFAULT 'planned',
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_maintenance_schedules_equipment
        FOREIGN KEY (equipment_id) REFERENCES equipment(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_maintenance_schedules_creator
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_maintenance_frequency_days
        CHECK (frequency_days IS NULL OR frequency_days > 0),

    CONSTRAINT chk_maintenance_usage_hour_interval
        CHECK (usage_hour_interval IS NULL OR usage_hour_interval > 0),

    INDEX idx_maintenance_schedules_equipment (equipment_id),
    INDEX idx_maintenance_schedules_date (scheduled_date),
    INDEX idx_maintenance_schedules_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 10. maintenance_logs
-- Stores performed maintenance/calibration results.
-- =====================================================
CREATE TABLE maintenance_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT NULL,
    equipment_id INT NOT NULL,
    technician_id INT NOT NULL,
    performed_date DATE NOT NULL,
    action_taken TEXT NOT NULL,
    cost DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    next_due_date DATE NULL,
    result_status ENUM('passed', 'needs_repair', 'failed') NOT NULL DEFAULT 'passed',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_maintenance_logs_schedule
        FOREIGN KEY (schedule_id) REFERENCES maintenance_schedules(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT fk_maintenance_logs_equipment
        FOREIGN KEY (equipment_id) REFERENCES equipment(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_maintenance_logs_technician
        FOREIGN KEY (technician_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_maintenance_log_cost
        CHECK (cost >= 0),

    INDEX idx_maintenance_logs_equipment (equipment_id),
    INDEX idx_maintenance_logs_technician (technician_id),
    INDEX idx_maintenance_logs_date (performed_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 11. damage_reports
-- Stores damage/lost equipment reports and penalty amount.
-- =====================================================
CREATE TABLE damage_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    record_id INT NULL,
    equipment_id INT NOT NULL,
    reported_by INT NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'low',
    description TEXT NOT NULL,
    repair_cost DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    penalty_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    status ENUM('reported', 'reviewing', 'charged', 'paid', 'closed') NOT NULL DEFAULT 'reported',
    reported_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_damage_reports_record
        FOREIGN KEY (record_id) REFERENCES borrow_records(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT fk_damage_reports_equipment
        FOREIGN KEY (equipment_id) REFERENCES equipment(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_damage_reports_reporter
        FOREIGN KEY (reported_by) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_damage_repair_cost
        CHECK (repair_cost >= 0),

    CONSTRAINT chk_damage_penalty_amount
        CHECK (penalty_amount >= 0),

    INDEX idx_damage_reports_equipment (equipment_id),
    INDEX idx_damage_reports_status (status),
    INDEX idx_damage_reports_reported_at (reported_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 12. penalty_payments
-- Stores payment records for damage penalties.
-- A damage report may have multiple payment records.
-- =====================================================
CREATE TABLE penalty_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    damage_report_id INT NOT NULL,
    paid_by INT NOT NULL,
    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    payment_method ENUM('cash', 'bank_transfer', 'other') NOT NULL DEFAULT 'cash',
    payment_status ENUM('unpaid', 'partial', 'paid') NOT NULL DEFAULT 'unpaid',
    paid_at DATETIME NULL,
    note TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_penalty_payments_damage_report
        FOREIGN KEY (damage_report_id) REFERENCES damage_reports(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_penalty_payments_paid_by
        FOREIGN KEY (paid_by) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_penalty_payment_amount
        CHECK (amount >= 0),

    INDEX idx_penalty_payments_damage_report (damage_report_id),
    INDEX idx_penalty_payments_paid_by (paid_by),
    INDEX idx_penalty_payments_status (payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SAMPLE DATA - realistic fictional data for demo
-- Default sample password for all users: 123456
-- Hash generated using PHP password_hash('123456', PASSWORD_DEFAULT)
-- Note: Names, phone numbers, emails, and suppliers below are fictional demo data,
-- but written to look like a real university lab management workflow.
-- =====================================================

INSERT INTO departments (department_code, department_name, description) VALUES
('DCE', 'Department of Computer Engineering', 'Manages embedded systems, IoT, and hardware practice laboratories.'),
('CSE', 'Department of Computer Science', 'Manages programming, AI, data science, and software engineering laboratories.'),
('MMD', 'Department of Multimedia Design', 'Manages multimedia production, photography, recording, and web design laboratories.'),
('ITSS', 'IT Services and Lab Support', 'Provides technical support, equipment maintenance, and asset tracking for teaching labs.');

INSERT INTO users (department_id, full_name, email, password_hash, role, phone, status) VALUES
((SELECT id FROM departments WHERE department_code = 'ITSS'), 'Nguyễn Hữu Khánh', 'khanh.nh@is-vnu.edu.vn', '$2y$12$zOvSE.MclqiExJHUUlJKJeCh4F6h.GuBd1TUPOEVpcz0VF3/iigI2', 'admin', '0986 321 045', 'active'),
((SELECT id FROM departments WHERE department_code = 'DCE'), 'Nguyễn Minh Anh', 'anh.nm230104@students.is-vnu.edu.vn', '$2y$12$zOvSE.MclqiExJHUUlJKJeCh4F6h.GuBd1TUPOEVpcz0VF3/iigI2', 'student', '0974 112 638', 'active'),
((SELECT id FROM departments WHERE department_code = 'DCE'), 'Trần Quốc Bảo', 'bao.tq220719@students.is-vnu.edu.vn', '$2y$12$zOvSE.MclqiExJHUUlJKJeCh4F6h.GuBd1TUPOEVpcz0VF3/iigI2', 'student', '0968 450 227', 'active'),
((SELECT id FROM departments WHERE department_code = 'MMD'), 'Lê Thảo Vy', 'vy.lt230288@students.is-vnu.edu.vn', '$2y$12$zOvSE.MclqiExJHUUlJKJeCh4F6h.GuBd1TUPOEVpcz0VF3/iigI2', 'student', '0912 780 563', 'active'),
((SELECT id FROM departments WHERE department_code = 'CSE'), 'Phạm Đức Long', 'long.pd220351@students.is-vnu.edu.vn', '$2y$12$zOvSE.MclqiExJHUUlJKJeCh4F6h.GuBd1TUPOEVpcz0VF3/iigI2', 'student', '0936 201 944', 'active'),
((SELECT id FROM departments WHERE department_code = 'ITSS'), 'Hoàng Thị Mai', 'mai.ht@is-vnu.edu.vn', '$2y$12$zOvSE.MclqiExJHUUlJKJeCh4F6h.GuBd1TUPOEVpcz0VF3/iigI2', 'technician', '0982 654 118', 'active'),
((SELECT id FROM departments WHERE department_code = 'ITSS'), 'Vũ Quang Huy', 'huy.vq@is-vnu.edu.vn', '$2y$12$zOvSE.MclqiExJHUUlJKJeCh4F6h.GuBd1TUPOEVpcz0VF3/iigI2', 'technician', '0971 908 337', 'active');

INSERT INTO labs (department_id, lab_code, lab_name, location, capacity, manager_user_id, status) VALUES
((SELECT id FROM departments WHERE department_code = 'DCE'), 'D401-IOT', 'IoT and Embedded Systems Lab', 'Building D - Room 401', 32, (SELECT id FROM users WHERE email = 'khanh.nh@is-vnu.edu.vn'), 'active'),
((SELECT id FROM departments WHERE department_code = 'CSE'), 'D402-AI', 'AI Computing Lab', 'Building D - Room 402', 28, (SELECT id FROM users WHERE email = 'khanh.nh@is-vnu.edu.vn'), 'active'),
((SELECT id FROM departments WHERE department_code = 'MMD'), 'A305-MEDIA', 'Multimedia Studio Lab', 'Building A - Room 305', 24, (SELECT id FROM users WHERE email = 'khanh.nh@is-vnu.edu.vn'), 'active'),
((SELECT id FROM departments WHERE department_code = 'DCE'), 'D403-HW', 'Hardware and Network Lab', 'Building D - Room 403', 30, (SELECT id FROM users WHERE email = 'mai.ht@is-vnu.edu.vn'), 'active');

INSERT INTO equipment_categories (category_name, description, maintenance_cycle_days, calibration_cycle_days) VALUES
('Microcontroller and SBC Kit', 'ESP32, Arduino, Raspberry Pi and other single-board computer kits used in embedded practice.', 120, NULL),
('Measurement and Testing Device', 'Oscilloscopes, digital multimeters, logic analyzers, and signal testing devices.', 180, 365),
('IoT Sensor Kit', 'Sensor boxes for temperature, humidity, gas, light, distance, and motion experiments.', 120, NULL),
('AI Workstation', 'High-performance desktop computers with dedicated GPU for AI and machine learning classes.', 365, NULL),
('Multimedia Capture Equipment', 'Cameras, tripods, microphones, audio interfaces, and video capture devices.', 180, NULL),
('Networking Equipment', 'Routers, switches, access points, and network testing kits used in computer network labs.', 180, NULL);

INSERT INTO suppliers (supplier_name, contact_person, phone, email, address) VALUES
('Hanoi Embedded Solutions JSC', 'Mr. Nguyễn Thành Long', '024 3628 1934', 'sales@hanoembedded.vn', 'No. 18 Duy Tan Street, Cau Giay, Hanoi'),
('VietEdu Lab Equipment Co., Ltd.', 'Ms. Phạm Thu Hương', '024 3782 4410', 'support@vietedulab.vn', 'Me Tri Ha Urban Area, Nam Tu Liem, Hanoi'),
('An Phat Computer Service', 'Mr. Đỗ Minh Tuấn', '024 7300 8888', 'b2b@anphat-demo.vn', 'Thai Ha Street, Dong Da, Hanoi'),
('MediaPro Hanoi', 'Ms. Lê Hồng Nhung', '024 3556 9021', 'contact@mediapro-demo.vn', 'Nguyen Trai Street, Thanh Xuan, Hanoi');

INSERT INTO equipment (asset_code, equipment_name, category_id, lab_id, supplier_id, serial_number, purchase_date, purchase_price, status, total_used_hours) VALUES
('D401-ESP32-001', 'ESP32 Development Kit Bundle - Box 01',
    (SELECT id FROM equipment_categories WHERE category_name = 'Microcontroller and SBC Kit'),
    (SELECT id FROM labs WHERE lab_code = 'D401-IOT'),
    (SELECT id FROM suppliers WHERE supplier_name = 'Hanoi Embedded Solutions JSC'),
    'ESP32-D401-2025-001', '2025-08-27', 680000.00, 'available', 42.50),

('D401-RPI4-002', 'Raspberry Pi 4 Training Set with Camera Module',
    (SELECT id FROM equipment_categories WHERE category_name = 'Microcontroller and SBC Kit'),
    (SELECT id FROM labs WHERE lab_code = 'D401-IOT'),
    (SELECT id FROM suppliers WHERE supplier_name = 'Hanoi Embedded Solutions JSC'),
    'RPI4-D401-2024-014', '2024-11-12', 2850000.00, 'available', 168.00),

('D401-ARD-003', 'Arduino Uno Classroom Kit - Box 03',
    (SELECT id FROM equipment_categories WHERE category_name = 'Microcontroller and SBC Kit'),
    (SELECT id FROM labs WHERE lab_code = 'D401-IOT'),
    (SELECT id FROM suppliers WHERE supplier_name = 'Hanoi Embedded Solutions JSC'),
    'ARD-D401-2025-003', '2025-09-03', 920000.00, 'available', 76.25),

('D401-OSC-004', 'Rigol DS1054Z Digital Oscilloscope',
    (SELECT id FROM equipment_categories WHERE category_name = 'Measurement and Testing Device'),
    (SELECT id FROM labs WHERE lab_code = 'D401-IOT'),
    (SELECT id FROM suppliers WHERE supplier_name = 'VietEdu Lab Equipment Co., Ltd.'),
    'DS1054Z-D401-0788', '2024-10-08', 17800000.00, 'borrowed', 312.75),

('D403-MUL-005', 'Fluke 117 Digital Multimeter',
    (SELECT id FROM equipment_categories WHERE category_name = 'Measurement and Testing Device'),
    (SELECT id FROM labs WHERE lab_code = 'D403-HW'),
    (SELECT id FROM suppliers WHERE supplier_name = 'VietEdu Lab Equipment Co., Ltd.'),
    'FL117-D403-2024-021', '2024-09-22', 5950000.00, 'available', 145.00),

('D401-SEN-006', 'IoT Sensor Box: DHT22, MQ-135, LDR, PIR',
    (SELECT id FROM equipment_categories WHERE category_name = 'IoT Sensor Kit'),
    (SELECT id FROM labs WHERE lab_code = 'D401-IOT'),
    (SELECT id FROM suppliers WHERE supplier_name = 'Hanoi Embedded Solutions JSC'),
    'SENBOX-D401-006', '2025-02-18', 1450000.00, 'damaged', 96.50),

('D402-AIWS-007', 'AI Workstation RTX 4070 - Seat 07',
    (SELECT id FROM equipment_categories WHERE category_name = 'AI Workstation'),
    (SELECT id FROM labs WHERE lab_code = 'D402-AI'),
    (SELECT id FROM suppliers WHERE supplier_name = 'An Phat Computer Service'),
    'AIWS-D402-4070-07', '2024-08-16', 48500000.00, 'maintenance', 934.00),

('A305-CAM-008', 'Canon EOS M50 Mark II Creator Kit',
    (SELECT id FROM equipment_categories WHERE category_name = 'Multimedia Capture Equipment'),
    (SELECT id FROM labs WHERE lab_code = 'A305-MEDIA'),
    (SELECT id FROM suppliers WHERE supplier_name = 'MediaPro Hanoi'),
    'EOSM50-A305-008', '2025-01-09', 21400000.00, 'available', 188.00),

('A305-AUD-009', 'Rode Wireless GO II Microphone Set',
    (SELECT id FROM equipment_categories WHERE category_name = 'Multimedia Capture Equipment'),
    (SELECT id FROM labs WHERE lab_code = 'A305-MEDIA'),
    (SELECT id FROM suppliers WHERE supplier_name = 'MediaPro Hanoi'),
    'RODE-A305-009', '2025-01-09', 7490000.00, 'available', 102.50),

('D403-RTR-010', 'MikroTik Router Practice Set - Group 02',
    (SELECT id FROM equipment_categories WHERE category_name = 'Networking Equipment'),
    (SELECT id FROM labs WHERE lab_code = 'D403-HW'),
    (SELECT id FROM suppliers WHERE supplier_name = 'VietEdu Lab Equipment Co., Ltd.'),
    'MTK-D403-010', '2024-12-04', 5200000.00, 'available', 221.00);

INSERT INTO borrow_requests (user_id, equipment_id, purpose, start_time, expected_return_time, status, approved_by, approved_at, note) VALUES
((SELECT id FROM users WHERE email = 'anh.nm230104@students.is-vnu.edu.vn'),
 (SELECT id FROM equipment WHERE asset_code = 'D401-ESP32-001'),
 'Borrow ESP32 kit to test MQTT data upload for the IoT soil moisture mini project.',
 '2026-05-20 08:30:00', '2026-05-20 11:30:00',
 'pending', NULL, NULL,
 'Student will use the kit during the morning practice slot in D401.'),

((SELECT id FROM users WHERE email = 'bao.tq220719@students.is-vnu.edu.vn'),
 (SELECT id FROM equipment WHERE asset_code = 'D401-OSC-004'),
 'Use oscilloscope to measure PWM output from ESP32 motor control circuit.',
 '2026-05-19 13:15:00', '2026-05-19 16:00:00',
 'approved', (SELECT id FROM users WHERE email = 'khanh.nh@is-vnu.edu.vn'), '2026-05-18 16:20:00',
 'Approved because the device is required for the embedded systems lab report.'),

((SELECT id FROM users WHERE email = 'long.pd220351@students.is-vnu.edu.vn'),
 (SELECT id FROM equipment WHERE asset_code = 'D401-RPI4-002'),
 'Use Raspberry Pi camera module to collect sample images for a computer vision exercise.',
 '2026-05-17 09:00:00', '2026-05-17 12:00:00',
 'completed', (SELECT id FROM users WHERE email = 'khanh.nh@is-vnu.edu.vn'), '2026-05-16 14:05:00',
 'Returned on the same day after the practical session.'),

((SELECT id FROM users WHERE email = 'vy.lt230288@students.is-vnu.edu.vn'),
 (SELECT id FROM equipment WHERE asset_code = 'A305-CAM-008'),
 'Borrow camera kit for filming a two-minute product advertisement assignment.',
 '2026-05-21 14:00:00', '2026-05-21 17:30:00',
 'pending', NULL, NULL,
 'Need one camera body, kit lens, and tripod from the multimedia studio.'),

((SELECT id FROM users WHERE email = 'anh.nm230104@students.is-vnu.edu.vn'),
 (SELECT id FROM equipment WHERE asset_code = 'D402-AIWS-007'),
 'Request workstation for training YOLO demo overnight.',
 '2026-05-20 18:00:00', '2026-05-21 08:00:00',
 'rejected', (SELECT id FROM users WHERE email = 'khanh.nh@is-vnu.edu.vn'), '2026-05-18 17:10:00',
 'Rejected because the workstation is currently under maintenance and overnight use is not allowed.'),

((SELECT id FROM users WHERE email = 'bao.tq220719@students.is-vnu.edu.vn'),
 (SELECT id FROM equipment WHERE asset_code = 'D401-SEN-006'),
 'Borrow sensor box to verify gas and light readings for IoT dashboard testing.',
 '2026-05-15 13:00:00', '2026-05-15 16:30:00',
 'completed', (SELECT id FROM users WHERE email = 'khanh.nh@is-vnu.edu.vn'), '2026-05-14 10:40:00',
 'Returned with a broken MQ-135 sensor pin, damage report was created.'),

((SELECT id FROM users WHERE email = 'vy.lt230288@students.is-vnu.edu.vn'),
 (SELECT id FROM equipment WHERE asset_code = 'A305-AUD-009'),
 'Borrow wireless microphone set for interview recording.',
 '2026-05-18 10:00:00', '2026-05-18 12:00:00',
 'cancelled', NULL, NULL,
 'Student cancelled because the interview schedule changed.');

INSERT INTO borrow_records (request_id, checkout_by, checkout_time, expected_return_time, checkin_by, checkin_time, condition_out, condition_in, used_hours, status, note) VALUES
((SELECT id FROM borrow_requests WHERE equipment_id = (SELECT id FROM equipment WHERE asset_code = 'D401-OSC-004') AND status = 'approved' LIMIT 1),
 (SELECT id FROM users WHERE email = 'mai.ht@is-vnu.edu.vn'),
 '2026-05-19 13:10:00', '2026-05-19 16:00:00',
 NULL, NULL,
 'Screen, probes, power cable, and calibration sticker checked before checkout.',
 NULL,
 0.00, 'checked_out',
 'Currently in use by student in D401.'),

((SELECT id FROM borrow_requests WHERE equipment_id = (SELECT id FROM equipment WHERE asset_code = 'D401-RPI4-002') AND status = 'completed' LIMIT 1),
 (SELECT id FROM users WHERE email = 'huy.vq@is-vnu.edu.vn'),
 '2026-05-17 08:55:00', '2026-05-17 12:00:00',
 (SELECT id FROM users WHERE email = 'huy.vq@is-vnu.edu.vn'), '2026-05-17 11:50:00',
 'Raspberry Pi board, camera ribbon, charger, and SD card were complete.',
 'Returned complete; camera module tested successfully after use.',
 2.90, 'returned',
 'No issue found during check-in.'),

((SELECT id FROM borrow_requests WHERE equipment_id = (SELECT id FROM equipment WHERE asset_code = 'D401-SEN-006') AND status = 'completed' LIMIT 1),
 (SELECT id FROM users WHERE email = 'mai.ht@is-vnu.edu.vn'),
 '2026-05-15 12:55:00', '2026-05-15 16:30:00',
 (SELECT id FROM users WHERE email = 'mai.ht@is-vnu.edu.vn'), '2026-05-15 16:45:00',
 'Sensor box was complete before checkout; MQ-135 module was working.',
 'MQ-135 sensor pin was bent and unstable after return.',
 3.75, 'damaged',
 'Damage report created after technician inspection.');

INSERT INTO maintenance_schedules (equipment_id, maintenance_type, scheduled_date, frequency_days, usage_hour_interval, status, created_by) VALUES
((SELECT id FROM equipment WHERE asset_code = 'D402-AIWS-007'),
 'maintenance', '2026-05-23', 180, 500, 'in_progress',
 (SELECT id FROM users WHERE email = 'mai.ht@is-vnu.edu.vn')),

((SELECT id FROM equipment WHERE asset_code = 'D401-OSC-004'),
 'calibration', '2026-06-03', 365, NULL, 'planned',
 (SELECT id FROM users WHERE email = 'huy.vq@is-vnu.edu.vn')),

((SELECT id FROM equipment WHERE asset_code = 'D403-MUL-005'),
 'calibration', '2026-05-10', 365, NULL, 'overdue',
 (SELECT id FROM users WHERE email = 'huy.vq@is-vnu.edu.vn')),

((SELECT id FROM equipment WHERE asset_code = 'A305-CAM-008'),
 'maintenance', '2026-06-12', 180, NULL, 'planned',
 (SELECT id FROM users WHERE email = 'mai.ht@is-vnu.edu.vn'));

INSERT INTO maintenance_logs (schedule_id, equipment_id, technician_id, performed_date, action_taken, cost, next_due_date, result_status) VALUES
((SELECT id FROM maintenance_schedules WHERE equipment_id = (SELECT id FROM equipment WHERE asset_code = 'D402-AIWS-007') LIMIT 1),
 (SELECT id FROM equipment WHERE asset_code = 'D402-AIWS-007'),
 (SELECT id FROM users WHERE email = 'mai.ht@is-vnu.edu.vn'),
 '2026-05-18',
 'Cleaned GPU fans, replaced thermal paste, updated NVIDIA driver, and ran a 45-minute stress test. System still needs one more reboot test before returning to service.',
 320000.00, '2026-11-18', 'needs_repair'),

(NULL,
 (SELECT id FROM equipment WHERE asset_code = 'D401-ARD-003'),
 (SELECT id FROM users WHERE email = 'huy.vq@is-vnu.edu.vn'),
 '2026-05-12',
 'Replaced two jumper wires, checked USB cable set, and verified all Arduino boards can upload blink sketch.',
 85000.00, '2026-09-12', 'passed');

INSERT INTO damage_reports (record_id, equipment_id, reported_by, severity, description, repair_cost, penalty_amount, status, reported_at) VALUES
((SELECT id FROM borrow_records WHERE request_id = (SELECT id FROM borrow_requests WHERE equipment_id = (SELECT id FROM equipment WHERE asset_code = 'D401-SEN-006') AND status = 'completed' LIMIT 1) LIMIT 1),
 (SELECT id FROM equipment WHERE asset_code = 'D401-SEN-006'),
 (SELECT id FROM users WHERE email = 'mai.ht@is-vnu.edu.vn'),
 'medium',
 'The MQ-135 sensor pin was bent after return. The sensor box still works, but gas reading is unstable and the module must be replaced before the next class.',
 260000.00, 200000.00, 'charged', '2026-05-15 17:05:00');

INSERT INTO penalty_payments (damage_report_id, paid_by, amount, payment_method, payment_status, paid_at, note) VALUES
((SELECT id FROM damage_reports WHERE equipment_id = (SELECT id FROM equipment WHERE asset_code = 'D401-SEN-006') LIMIT 1),
 (SELECT id FROM users WHERE email = 'bao.tq220719@students.is-vnu.edu.vn'),
 100000.00, 'bank_transfer', 'partial', '2026-05-16 09:20:00',
 'Student paid the first part of the penalty after technician confirmation. Remaining amount: 100000 VND.');

-- =====================================================
-- Useful test queries
-- =====================================================

-- Login demo accounts, all use password: 123456
-- Admin:      khanh.nh@is-vnu.edu.vn
-- Technician: mai.ht@is-vnu.edu.vn
-- Student:    anh.nm230104@students.is-vnu.edu.vn
-- Student:    bao.tq220719@students.is-vnu.edu.vn

-- SELECT * FROM departments;
-- SELECT * FROM users;
-- SELECT * FROM labs;
-- SELECT * FROM equipment;
-- SELECT * FROM borrow_requests;
-- SELECT * FROM borrow_records;
-- SELECT * FROM maintenance_schedules;
-- SELECT * FROM damage_reports;
-- SELECT * FROM penalty_payments;

-- Borrow request list with related names:
-- SELECT
--     br.id AS request_id,
--     u.full_name AS requester,
--     e.asset_code,
--     e.equipment_name,
--     br.purpose,
--     br.start_time,
--     br.expected_return_time,
--     br.status,
--     approver.full_name AS approved_by
-- FROM borrow_requests br
-- JOIN users u ON br.user_id = u.id
-- JOIN equipment e ON br.equipment_id = e.id
-- LEFT JOIN users approver ON br.approved_by = approver.id
-- ORDER BY br.id DESC;

-- Equipment overview with lab and category:
-- SELECT
--     e.asset_code,
--     e.equipment_name,
--     ec.category_name,
--     l.lab_code,
--     l.lab_name,
--     e.status,
--     e.total_used_hours
-- FROM equipment e
-- JOIN equipment_categories ec ON e.category_id = ec.id
-- JOIN labs l ON e.lab_id = l.id
-- ORDER BY l.lab_code, e.asset_code;
