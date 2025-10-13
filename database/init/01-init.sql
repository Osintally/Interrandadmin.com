-- Initialize database with proper charset and collation
CREATE DATABASE IF NOT EXISTS interrand_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Create user if not exists
CREATE USER IF NOT EXISTS 'interrand_user'@'%' IDENTIFIED BY 'interrand_password';

-- Grant privileges
GRANT ALL PRIVILEGES ON interrand_db.* TO 'interrand_user'@'%';

-- Flush privileges
FLUSH PRIVILEGES;

-- Use the database
USE interrand_db;

-- Set SQL mode for better compatibility
SET sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
