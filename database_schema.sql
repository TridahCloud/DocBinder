-- DocBinder Database Schema
-- Created for Tridah non-profit organization

CREATE DATABASE IF NOT EXISTS docbinder;
USE docbinder;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Binders table
CREATE TABLE binders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Documents table (files within binders)
CREATE TABLE documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    binder_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    intro_text TEXT,
    outro_text TEXT,
    file_path VARCHAR(500),
    file_type ENUM('pdf', 'image', 'text') NOT NULL,
    text_content LONGTEXT,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (binder_id) REFERENCES binders(id) ON DELETE CASCADE
);

-- User sessions table
CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Shared binders table (for email-based sharing functionality)
CREATE TABLE shared_binders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    binder_id INT NOT NULL,
    shared_by_user_id INT NOT NULL,
    shared_with_email VARCHAR(100) NOT NULL,
    access_token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (binder_id) REFERENCES binders(id) ON DELETE CASCADE,
    FOREIGN KEY (shared_by_user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_share (binder_id, shared_with_email)
);

-- Indexes for better performance
CREATE INDEX idx_binders_user_id ON binders(user_id);
CREATE INDEX idx_documents_binder_id ON documents(binder_id);
CREATE INDEX idx_documents_sort_order ON documents(binder_id, sort_order);
CREATE INDEX idx_user_sessions_token ON user_sessions(session_token);
CREATE INDEX idx_shared_binders_token ON shared_binders(access_token);
