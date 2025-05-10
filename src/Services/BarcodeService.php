<?php
namespace EcwidApp\Services;

use EcwidApp\Api\EcwidApi;

class BarcodeService {
    private $api;
    private $prefix;
    private $lastUsedNumber;
    private $db;
    
    public function __construct(EcwidApi $api) {
        $this->api = $api;
        try {
            if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER') && defined('DB_PASS')) {
                $this->db = new \PDO(
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                    DB_USER,
                    DB_PASS,
                    [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
                );
                
                $this->initDatabase();
                $this->loadLastUsedNumber();
            } else {
                throw new \Exception("Database configuration not found");
            }
        } catch (\PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new \RuntimeException("Could not initialize barcode service: " . $e->getMessage());
        }
    }
    
    public function generateBarcodes($products) {
        $updates = [];
        foreach ($products as $product) {
            if (empty($product['sku'])) {
                $barcode = $this->generateBarcode();
                $updates[] = [
                    'id' => $product['id'],
                    'data' => ['sku' => $barcode]
                ];
            }
        }
        
        if (!empty($updates)) {
            return $this->api->batchUpdateProducts($updates);
        }
        
        return ['success' => true, 'message' => 'No products needed barcodes'];
    }
    
    public function generateBarcode() {
        $this->lastUsedNumber++;
        
        // Generate EAN-13 barcode
        $barcode = $this->prefix . str_pad($this->lastUsedNumber, 7, '0', STR_PAD_LEFT);
        $checkDigit = $this->calculateCheckDigit($barcode);
        $barcode .= $checkDigit;
        
        // Save the last used number
        $this->saveLastUsedNumber();
        
        return $barcode;
    }
    
    private function calculateCheckDigit($barcode) {
        $sum = 0;
        $length = strlen($barcode);
        
        for ($i = 0; $i < $length; $i++) {
            $digit = intval($barcode[$i]);
            $sum += ($i % 2 == 0) ? $digit : $digit * 3;
        }
        
        $checkDigit = (10 - ($sum % 10)) % 10;
        return $checkDigit;
    }
    
    private function initDatabase() {
        // Create settings table if not exists
        $this->db->exec("CREATE TABLE IF NOT EXISTS settings (
            `key` VARCHAR(50) PRIMARY KEY,
            `value` TEXT NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        // Initialize prefix if not set
        $stmt = $this->db->prepare("SELECT value FROM settings WHERE `key` = 'barcode_prefix'");
        $stmt->execute();
        if (!$stmt->fetch()) {
            $stmt = $this->db->prepare("INSERT INTO settings (`key`, value) VALUES ('barcode_prefix', '200')");
            $stmt->execute();
        }
        
        // Initialize last used number if not set
        $stmt = $this->db->prepare("SELECT value FROM settings WHERE `key` = 'last_barcode_number'");
        $stmt->execute();
        if (!$stmt->fetch()) {
            $stmt = $this->db->prepare("INSERT INTO settings (`key`, value) VALUES ('last_barcode_number', '0')");
            $stmt->execute();
        }
    }
    
    private function loadLastUsedNumber() {
        // Load prefix
        $stmt = $this->db->prepare("SELECT value FROM settings WHERE `key` = 'barcode_prefix'");
        $stmt->execute();
        $result = $stmt->fetch();
        $this->prefix = $result['value'];
        
        // Load last used number
        $stmt = $this->db->prepare("SELECT value FROM settings WHERE `key` = 'last_barcode_number'");
        $stmt->execute();
        $result = $stmt->fetch();
        $this->lastUsedNumber = intval($result['value']);
    }
    
    private function saveLastUsedNumber() {
        $stmt = $this->db->prepare("UPDATE settings SET value = :value WHERE `key` = 'last_barcode_number'");
        $stmt->execute([':value' => $this->lastUsedNumber]);
    }
    
    public function setPrefix($prefix) {
        if (strlen($prefix) !== 3 || !is_numeric($prefix)) {
            throw new \InvalidArgumentException('Prefix must be a 3-digit number');
        }
        
        $stmt = $this->db->prepare("UPDATE settings SET value = :value WHERE `key` = 'barcode_prefix'");
        $stmt->execute([':value' => $prefix]);
        $this->prefix = $prefix;
    }
}
