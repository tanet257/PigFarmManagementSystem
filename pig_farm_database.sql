-- ============================================================
-- PIG FARM MANAGEMENT SYSTEM - DATABASE CREATION SCRIPT
-- ============================================================
-- Last Updated: November 11, 2025
-- Version: 1.0
-- Description: Complete SQL script for Pig Farm Management System
-- Use this script in DBeaver to create all tables and relationships
-- ============================================================

-- Step 1: Create Database
-- ============================================================
CREATE DATABASE IF NOT EXISTS pig_farm_management;
USE pig_farm_management;

-- Enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- CORE TABLES
-- ============================================================

-- Table: ROLES
-- Purpose: Store user role definitions
-- ============================================================
CREATE TABLE IF NOT EXISTS roles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: PERMISSIONS
-- Purpose: Store permission definitions
-- ============================================================
CREATE TABLE IF NOT EXISTS permissions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: FARMS
-- Purpose: Store farm information
-- ============================================================
CREATE TABLE IF NOT EXISTS farms (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    farm_name VARCHAR(255) NOT NULL,
    owner_name VARCHAR(255) NOT NULL,
    location TEXT,
    phone VARCHAR(20),
    email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_farm_name (farm_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: USERS
-- Purpose: Store user accounts and authentication
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    farm_id BIGINT,
    usertype VARCHAR(50),
    status ENUM('pending', 'active', 'inactive', 'approved', 'rejected') DEFAULT 'pending',
    approved_at TIMESTAMP NULL,
    approved_by BIGINT,
    email_verified_at TIMESTAMP NULL,
    two_factor_secret VARCHAR(255),
    two_factor_recovery_codes TEXT,
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_email (email),
    INDEX idx_farm_id (farm_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: BARNS
-- Purpose: Store barn information per farm
-- ============================================================
CREATE TABLE IF NOT EXISTS barns (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    farm_id BIGINT NOT NULL,
    barn_code VARCHAR(50) NOT NULL,
    barn_name VARCHAR(255) NOT NULL,
    capacity INT NOT NULL,
    location TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    UNIQUE KEY uq_barn_code (farm_id, barn_code),
    INDEX idx_farm_id (farm_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: PENS
-- Purpose: Store individual pens (pigsty) in barns
-- ============================================================
CREATE TABLE IF NOT EXISTS pens (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    barn_id BIGINT NOT NULL,
    pen_code VARCHAR(50) NOT NULL,
    pen_name VARCHAR(255) NOT NULL,
    capacity INT NOT NULL,
    location TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (barn_id) REFERENCES barns(id) ON DELETE CASCADE,
    UNIQUE KEY uq_pen_code (barn_id, pen_code),
    INDEX idx_barn_id (barn_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- BATCH MANAGEMENT TABLES
-- ============================================================

-- Table: BATCHES
-- Purpose: Store pig batch information
-- ============================================================
CREATE TABLE IF NOT EXISTS batches (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    farm_id BIGINT NOT NULL,
    batch_code VARCHAR(100) NOT NULL UNIQUE,
    start_date DATE NOT NULL,
    expected_end DATE,
    initial_quantity INT NOT NULL,
    starting_weight DECIMAL(8,2) NOT NULL,
    target_weight DECIMAL(8,2) NOT NULL,
    status ENUM('active', 'closed', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    INDEX idx_batch_code (batch_code),
    INDEX idx_farm_id (farm_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: BATCH_PEN_ALLOCATION
-- Purpose: Track pig allocation to pens
-- ============================================================
CREATE TABLE IF NOT EXISTS batch_pen_allocation (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    batch_id BIGINT NOT NULL,
    pen_id BIGINT NOT NULL,
    quantity_allocated INT NOT NULL,
    allocated_date DATE NOT NULL,
    removed_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE CASCADE,
    FOREIGN KEY (pen_id) REFERENCES pens(id) ON DELETE CASCADE,
    INDEX idx_batch_id (batch_id),
    INDEX idx_pen_id (pen_id),
    UNIQUE KEY uq_batch_pen (batch_id, pen_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: DAIRY_RECORDS
-- Purpose: Daily tracking of batch
-- ============================================================
CREATE TABLE IF NOT EXISTS dairy_records (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    batch_id BIGINT NOT NULL,
    record_date DATE NOT NULL,
    quantity_pigs INT NOT NULL,
    avg_weight_per_pig DECIMAL(8,2) NOT NULL,
    feed_consumed_kg DECIMAL(10,2) NOT NULL,
    sick_count INT DEFAULT 0,
    dead_count INT DEFAULT 0,
    health_notes TEXT,
    recorded_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_batch_id (batch_id),
    INDEX idx_record_date (record_date),
    INDEX idx_batch_date (batch_id, record_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: BATCH_METRICS
-- Purpose: Calculated KPI metrics per batch
-- ============================================================
CREATE TABLE IF NOT EXISTS batch_metrics (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    batch_id BIGINT NOT NULL UNIQUE,
    adg DECIMAL(6,3) NOT NULL DEFAULT 0,
    fcr DECIMAL(6,3) NOT NULL DEFAULT 0,
    fcg DECIMAL(8,2) NOT NULL DEFAULT 0,
    mortality_rate DECIMAL(5,2) NOT NULL DEFAULT 0,
    morbidity_rate DECIMAL(5,2),
    ending_avg_weight DECIMAL(8,2),
    days_in_farm INT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE CASCADE,
    INDEX idx_batch_id (batch_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: PIG_ENTRY_RECORD
-- Purpose: Initial pig entry into batch
-- ============================================================
CREATE TABLE IF NOT EXISTS pig_entry_record (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    batch_id BIGINT NOT NULL,
    farm_id BIGINT NOT NULL,
    pig_entry_date DATE NOT NULL,
    total_pig_amount INT NOT NULL,
    total_pig_weight DECIMAL(10,2) NOT NULL,
    total_pig_price DECIMAL(12,2) NOT NULL,
    weight_per_pig DECIMAL(8,2) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    supplier_name VARCHAR(255),
    created_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE CASCADE,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_batch_id (batch_id),
    INDEX idx_farm_id (farm_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: PIG_DEATH
-- Purpose: Track pig deaths
-- ============================================================
CREATE TABLE IF NOT EXISTS pig_death (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    batch_id BIGINT NOT NULL,
    death_date DATE NOT NULL,
    quantity_died INT NOT NULL DEFAULT 1,
    reason VARCHAR(255),
    notes TEXT,
    recorded_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_batch_id (batch_id),
    INDEX idx_death_date (death_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: DAILY_TREATMENT_LOG
-- Purpose: Track daily treatments
-- ============================================================
CREATE TABLE IF NOT EXISTS daily_treatment_log (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    batch_id BIGINT NOT NULL,
    treatment_date DATE NOT NULL,
    treatment_type VARCHAR(100),
    quantity_treated INT NOT NULL,
    medicine_used VARCHAR(255),
    dosage VARCHAR(100),
    notes TEXT,
    recorded_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_batch_id (batch_id),
    INDEX idx_treatment_date (treatment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- FINANCIAL TABLES
-- ============================================================

-- Table: COSTS
-- Purpose: Store all batch expenses
-- ============================================================
CREATE TABLE IF NOT EXISTS costs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    farm_id BIGINT NOT NULL,
    batch_id BIGINT NOT NULL,
    pig_entry_record_id BIGINT,
    cost_type ENUM('feed', 'medicine', 'wage', 'electric_bill', 'water_bill', 'shipping', 'piglet', 'other') NOT NULL,
    item_code VARCHAR(100),
    quantity INT,
    unit VARCHAR(50),
    price_per_unit DECIMAL(10,2),
    amount DECIMAL(12,2) NOT NULL,
    total_price DECIMAL(12,2) NOT NULL,
    receipt_file TEXT,
    note TEXT,
    date DATE NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE CASCADE,
    FOREIGN KEY (pig_entry_record_id) REFERENCES pig_entry_record(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_farm_id (farm_id),
    INDEX idx_batch_id (batch_id),
    INDEX idx_cost_type (cost_type),
    INDEX idx_status (status),
    INDEX idx_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: COST_PAYMENT
-- Purpose: Payment records for costs
-- ============================================================
CREATE TABLE IF NOT EXISTS cost_payment (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    cost_id BIGINT NOT NULL,
    payment_number VARCHAR(100) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method VARCHAR(50),
    reference_number VARCHAR(100),
    bank_name VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    receipt_file TEXT,
    notes TEXT,
    cost_type VARCHAR(50),
    recorded_by BIGINT,
    approved_by BIGINT,
    approved_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cost_id) REFERENCES costs(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_cost_id (cost_id),
    INDEX idx_status (status),
    INDEX idx_payment_date (payment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: CUSTOMERS
-- Purpose: Store customer information
-- ============================================================
CREATE TABLE IF NOT EXISTS customers (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    farm_id BIGINT NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_code VARCHAR(100),
    contact_person VARCHAR(255),
    phone VARCHAR(20),
    email VARCHAR(255),
    address TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    INDEX idx_farm_id (farm_id),
    INDEX idx_customer_name (customer_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: PIG_SALES
-- Purpose: Record pig sales transactions
-- ============================================================
CREATE TABLE IF NOT EXISTS pig_sales (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    batch_id BIGINT,
    farm_id BIGINT NOT NULL,
    pen_id BIGINT,
    customer_id BIGINT,
    sale_number VARCHAR(100) NOT NULL UNIQUE,
    date DATE NOT NULL,
    quantity INT NOT NULL,
    total_weight DECIMAL(10,2) NOT NULL,
    actual_weight DECIMAL(10,2),
    price_per_kg DECIMAL(10,2) NOT NULL,
    price_per_pig DECIMAL(12,2),
    total_price DECIMAL(12,2) NOT NULL,
    shipping_cost DECIMAL(10,2) DEFAULT 0,
    net_total DECIMAL(12,2) NOT NULL,
    payment_method VARCHAR(50),
    payment_status ENUM('รอชำระ', 'ชำระแล้ว', 'ชำระบางส่วน', 'เกินกำหนด', 'ยกเลิกการขาย') DEFAULT 'รอชำระ',
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    receipt_file TEXT,
    note TEXT,
    created_by BIGINT,
    approved_by BIGINT,
    approved_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE SET NULL,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (pen_id) REFERENCES pens(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_batch_id (batch_id),
    INDEX idx_farm_id (farm_id),
    INDEX idx_status (status),
    INDEX idx_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: PAYMENT
-- Purpose: Payment records for pig sales
-- ============================================================
CREATE TABLE IF NOT EXISTS payment (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    pig_sale_id BIGINT NOT NULL,
    payment_number VARCHAR(100) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    reference_number VARCHAR(100),
    bank_name VARCHAR(255),
    receipt_file TEXT,
    note TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    recorded_by BIGINT,
    approved_by BIGINT,
    approved_date DATE,
    rejected_by BIGINT,
    rejected_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pig_sale_id) REFERENCES pig_sales(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (rejected_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_pig_sale_id (pig_sale_id),
    INDEX idx_status (status),
    INDEX idx_payment_date (payment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: REVENUE
-- Purpose: Revenue records from sales
-- ============================================================
CREATE TABLE IF NOT EXISTS revenue (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    farm_id BIGINT NOT NULL,
    batch_id BIGINT NOT NULL,
    pig_sale_id BIGINT NOT NULL UNIQUE,
    revenue_type VARCHAR(50),
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_revenue DECIMAL(12,2) NOT NULL,
    discount DECIMAL(10,2) DEFAULT 0,
    net_revenue DECIMAL(12,2) NOT NULL,
    revenue_date DATE NOT NULL,
    status ENUM('pending', 'approved') DEFAULT 'pending',
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE CASCADE,
    FOREIGN KEY (pig_sale_id) REFERENCES pig_sales(id) ON DELETE CASCADE,
    INDEX idx_batch_id (batch_id),
    INDEX idx_farm_id (farm_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: PROFIT
-- Purpose: Profit calculations per batch
-- ============================================================
CREATE TABLE IF NOT EXISTS profit (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    farm_id BIGINT NOT NULL,
    batch_id BIGINT NOT NULL UNIQUE,
    revenue_id BIGINT,
    total_revenue DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
    gross_profit DECIMAL(12,2) NOT NULL DEFAULT 0,
    profit_margin DECIMAL(8,2) NOT NULL DEFAULT 0,
    adg DECIMAL(6,3) DEFAULT 0,
    fcr DECIMAL(6,3) DEFAULT 0,
    fcg DECIMAL(8,2) DEFAULT 0,
    ending_avg_weight DECIMAL(8,2),
    days_in_farm INT DEFAULT 0,
    profit_status VARCHAR(50),
    feed_cost DECIMAL(12,2) DEFAULT 0,
    medicine_cost DECIMAL(12,2) DEFAULT 0,
    transport_cost DECIMAL(12,2) DEFAULT 0,
    labor_cost DECIMAL(12,2) DEFAULT 0,
    utility_cost DECIMAL(12,2) DEFAULT 0,
    other_cost DECIMAL(12,2) DEFAULT 0,
    period_start DATE,
    period_end DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE CASCADE,
    FOREIGN KEY (revenue_id) REFERENCES revenue(id) ON DELETE SET NULL,
    INDEX idx_batch_id (batch_id),
    INDEX idx_farm_id (farm_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- INVENTORY TABLES
-- ============================================================

-- Table: STOREHOUSE
-- Purpose: Inventory items management
-- ============================================================
CREATE TABLE IF NOT EXISTS storehouse (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    farm_id BIGINT NOT NULL,
    item_code VARCHAR(100) NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    item_type ENUM('feed', 'medicine', 'supplies', 'equipment', 'other') NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    min_quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    supplier VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_updated DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    UNIQUE KEY uq_item_code (farm_id, item_code),
    INDEX idx_farm_id (farm_id),
    INDEX idx_item_type (item_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: INVENTORY_MOVEMENT
-- Purpose: Track inventory in/out movements
-- ============================================================
CREATE TABLE IF NOT EXISTS inventory_movement (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    batch_id BIGINT,
    storehouse_id BIGINT NOT NULL,
    date DATE NOT NULL,
    change_type ENUM('in', 'out') NOT NULL,
    quantity_changed INT NOT NULL,
    cost_per_unit DECIMAL(10,2) NOT NULL,
    total_cost DECIMAL(12,2) NOT NULL,
    reason VARCHAR(255),
    recorded_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE SET NULL,
    FOREIGN KEY (storehouse_id) REFERENCES storehouse(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_batch_id (batch_id),
    INDEX idx_storehouse_id (storehouse_id),
    INDEX idx_change_type (change_type),
    INDEX idx_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: STOREHOUSE_AUDIT_LOG
-- Purpose: Audit trail for inventory changes
-- ============================================================
CREATE TABLE IF NOT EXISTS storehouse_audit_log (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    storehouse_id BIGINT NOT NULL,
    old_quantity INT,
    new_quantity INT NOT NULL,
    change_type ENUM('in', 'out', 'adjust') NOT NULL,
    reason VARCHAR(255),
    changed_by BIGINT,
    changed_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (storehouse_id) REFERENCES storehouse(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_storehouse_id (storehouse_id),
    INDEX idx_changed_at (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TREATMENT TABLES
-- ============================================================

-- Table: BATCH_TREATMENT
-- Purpose: Store batch treatment records
-- ============================================================
CREATE TABLE IF NOT EXISTS batch_treatment (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    batch_id BIGINT NOT NULL,
    treatment_date DATE NOT NULL,
    treatment_type VARCHAR(100),
    quantity_treated INT,
    medicine_name VARCHAR(255),
    dosage VARCHAR(100),
    result VARCHAR(255),
    notes TEXT,
    recorded_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_batch_id (batch_id),
    INDEX idx_treatment_date (treatment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: DAIRY_STOREHOUSE_USE
-- Purpose: Track storehouse items used daily
-- ============================================================
CREATE TABLE IF NOT EXISTS dairy_storehouse_use (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    dairy_record_id BIGINT NOT NULL,
    storehouse_id BIGINT NOT NULL,
    quantity_used DECIMAL(10,2) NOT NULL,
    unit_cost DECIMAL(10,2) NOT NULL,
    total_cost DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dairy_record_id) REFERENCES dairy_records(id) ON DELETE CASCADE,
    FOREIGN KEY (storehouse_id) REFERENCES storehouse(id) ON DELETE CASCADE,
    INDEX idx_dairy_record_id (dairy_record_id),
    INDEX idx_storehouse_id (storehouse_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: DAIRY_TREATMENT
-- Purpose: Track treatments used in daily records
-- ============================================================
CREATE TABLE IF NOT EXISTS dairy_treatment (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    dairy_record_id BIGINT NOT NULL,
    treatment_type VARCHAR(100),
    medicine VARCHAR(255),
    quantity INT,
    dosage VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dairy_record_id) REFERENCES dairy_records(id) ON DELETE CASCADE,
    INDEX idx_dairy_record_id (dairy_record_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SYSTEM TABLES
-- ============================================================

-- Table: NOTIFICATIONS
-- Purpose: User notification messages
-- ============================================================
CREATE TABLE IF NOT EXISTS notifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    related_model VARCHAR(100),
    related_model_id BIGINT,
    related_user_id BIGINT,
    url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_user_read (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: ROLE_USER (Many-to-Many relationship)
-- Purpose: Store user-role relationships
-- ============================================================
CREATE TABLE IF NOT EXISTS role_user (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    role_id BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    UNIQUE KEY uq_user_role (user_id, role_id),
    INDEX idx_user_id (user_id),
    INDEX idx_role_id (role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: ROLE_PERMISSION (Many-to-Many relationship)
-- Purpose: Store role-permission relationships
-- ============================================================
CREATE TABLE IF NOT EXISTS role_permission (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    role_id BIGINT NOT NULL,
    permission_id BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    UNIQUE KEY uq_role_permission (role_id, permission_id),
    INDEX idx_role_id (role_id),
    INDEX idx_permission_id (permission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- INSERT INITIAL DATA (Roles and Permissions)
-- ============================================================

-- Insert Roles
INSERT INTO roles (name, description, created_at, updated_at) VALUES
('admin', 'ผู้ดูแลระบบ - มีสิทธิ์ทั้งหมด', NOW(), NOW()),
('staff', 'พนักงาน - บันทึกข้อมูลประจำวัน', NOW(), NOW()),
('manager', 'ผู้จัดการ - ดูรายงานและจัดการบาง', NOW(), NOW()),
('viewer', 'ผู้ชม - อ่านอย่างเดียว', NOW(), NOW())
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- Insert Permissions
INSERT INTO permissions (name, description, created_at) VALUES
('view_pig', 'ดูข้อมูลหมู', NOW()),
('create_pig', 'เพิ่มข้อมูลหมู', NOW()),
('edit_pig', 'แก้ไขข้อมูลหมู', NOW()),
('delete_pig', 'ลบข้อมูลหมู', NOW()),
('manage_feed', 'จัดการอาหาร', NOW()),
('manage_medicine', 'จัดการยา', NOW()),
('view_reports', 'ดูรายงาน', NOW()),
('manage_users', 'จัดการผู้ใช้', NOW()),
('assign_roles', 'กำหนด Role', NOW()),
('manage_notifications', 'จัดการแจ้งเตือน', NOW()),
('access_settings', 'เข้าถึงการตั้งค่า', NOW())
ON DUPLICATE KEY UPDATE name = name;

-- ============================================================
-- CREATE INDEXES FOR BETTER PERFORMANCE
-- ============================================================

-- Create additional composite indexes for common queries
CREATE INDEX idx_batch_status_date ON batches (status, start_date);
CREATE INDEX idx_cost_batch_date ON costs (batch_id, date);
CREATE INDEX idx_revenue_batch_date ON revenue (batch_id, revenue_date);
CREATE INDEX idx_dairy_batch_date ON dairy_records (batch_id, record_date);
CREATE INDEX idx_inventory_storehouse_date ON inventory_movement (storehouse_id, date);

-- ============================================================
-- COMPLETION MESSAGE
-- ============================================================
-- All tables created successfully!
-- The database is ready for use.
-- ============================================================
