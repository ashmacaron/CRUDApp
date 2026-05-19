-- ============================================================
-- PHP CRUD App - Database Schema
-- Run this file to set up the database structure
-- ============================================================

CREATE DATABASE IF NOT EXISTS crud_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE crud_app;

-- Users table: stores auth credentials
CREATE TABLE IF NOT EXISTS users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)        NOT NULL,
    email       VARCHAR(150)        NOT NULL UNIQUE,
    password    VARCHAR(255)        NOT NULL,  -- bcrypt hash via password_hash()
    role        ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at  TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin account
-- Email:    admin@admin.com
-- Password: Admin@1234
-- NOTE: After import, see README if login fails (regenerate hash for your PHP version)
INSERT INTO users (name, email, password, role) VALUES
(
    'Admin',
    'admin@admin.com',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin'
);
