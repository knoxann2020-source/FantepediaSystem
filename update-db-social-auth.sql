-- Run this in phpMyAdmin (localhost/phpmyadmin) on 'fs' database
-- Backup first!

USE fs;

ALTER TABLE users 
ADD COLUMN google_id VARCHAR(255) NULL,
ADD COLUMN facebook_id VARCHAR(255) NULL,
ADD COLUMN provider ENUM('email', 'google', 'facebook') DEFAULT 'email';

-- Verify
DESCRIBE users;

-- Expected new columns:
-- google_id, facebook_id, provider

