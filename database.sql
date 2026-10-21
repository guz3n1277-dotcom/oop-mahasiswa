
CREATE DATABASE IF NOT EXISTS akademik
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE akademik;

CREATE TABLE IF NOT EXISTS mahasiswa (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(30) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    alamat TEXT NOT NULL,
    fakultas VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS security_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    activity VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_security_logs_created_at (created_at),
    INDEX idx_security_logs_username (username),
    INDEX idx_security_logs_activity (activity)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS login_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempts INT UNSIGNED NOT NULL DEFAULT 0,
    first_attempt_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    locked_until DATETIME NULL,
    UNIQUE KEY unique_login_attempt (username, ip_address)
) ENGINE=InnoDB;
