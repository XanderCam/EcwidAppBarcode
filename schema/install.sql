-- Create tokens table for OAuth
CREATE TABLE IF NOT EXISTS tokens (
    store_id INT PRIMARY KEY,
    access_token VARCHAR(255) NOT NULL,
    refresh_token VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create settings table for app configuration
CREATE TABLE IF NOT EXISTS settings (
    `key` VARCHAR(50) PRIMARY KEY,
    `value` TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default settings
INSERT INTO settings (`key`, `value`) VALUES
('barcode_prefix', '200'),
('last_barcode_number', '0')
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- Create barcode_logs table for tracking
CREATE TABLE IF NOT EXISTS barcode_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    product_id INT NOT NULL,
    barcode VARCHAR(13) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_barcode (barcode),
    INDEX idx_store_product (store_id, product_id)
);
