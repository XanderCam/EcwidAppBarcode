<?php
namespace EcwidApp\Services;

class AuthService {
    private $db;
    
    public function __construct() {
        try {
            if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER') && defined('DB_PASS')) {
                $this->db = new \PDO(
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                    DB_USER,
                    DB_PASS,
                    [
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
                    ]
                );
                
                // Create tokens table if not exists
                $this->initDatabase();
            }
        } catch (\PDOException $e) {
            // Log error but don't crash
            error_log("Database connection failed: " . $e->getMessage());
        }
    }
    
    public function getAuthUrl() {
        $params = [
            'client_id' => CLIENT_ID,
            'redirect_uri' => APP_URL,
            'response_type' => 'code',
            'scope' => implode(' ', API_SCOPES)
        ];
        
        return ECWID_OAUTH_URL . '/authorize?' . http_build_query($params);
    }
    
    public function handleCallback($code) {
        $params = [
            'client_id' => CLIENT_ID,
            'client_secret' => CLIENT_SECRET,
            'code' => $code,
            'redirect_uri' => APP_URL,
            'grant_type' => 'authorization_code'
        ];
        
        $ch = curl_init(ECWID_OAUTH_URL . '/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        
        $response = curl_exec($ch);
        $data = json_decode($response, true);
        
        if (!isset($data['access_token'])) {
            throw new \Exception('Failed to get access token: ' . 
                ($data['error_description'] ?? 'Unknown error'));
        }
        
        // Store tokens in database
        $this->saveTokens($data['store_id'], $data['access_token'], $data['refresh_token'] ?? null);
        
        // Store in session for current request
        $_SESSION['store_id'] = $data['store_id'];
        $_SESSION['access_token'] = $data['access_token'];
        
        return true;
    }
    
    public function isAuthenticated() {
        return isset($_SESSION['access_token']) && isset($_SESSION['store_id']);
    }
    
    public function refreshToken($storeId) {
        $tokens = $this->getTokens($storeId);
        if (!$tokens || !isset($tokens['refresh_token'])) {
            return false;
        }
        
        $params = [
            'client_id' => CLIENT_ID,
            'client_secret' => CLIENT_SECRET,
            'refresh_token' => $tokens['refresh_token'],
            'grant_type' => 'refresh_token'
        ];
        
        $ch = curl_init(ECWID_OAUTH_URL . '/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        
        $response = curl_exec($ch);
        $data = json_decode($response, true);
        
        if (isset($data['access_token'])) {
            $this->saveTokens($storeId, $data['access_token'], $data['refresh_token'] ?? null);
            return $data['access_token'];
        }
        
        return false;
    }
    
    private function initDatabase() {
        $this->db->exec("CREATE TABLE IF NOT EXISTS tokens (
            store_id INT PRIMARY KEY,
            access_token VARCHAR(255) NOT NULL,
            refresh_token VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    }
    
    private function saveTokens($storeId, $accessToken, $refreshToken = null) {
        $sql = "INSERT INTO tokens (store_id, access_token, refresh_token) 
                VALUES (:store_id, :access_token, :refresh_token)
                ON DUPLICATE KEY UPDATE 
                access_token = VALUES(access_token),
                refresh_token = VALUES(refresh_token),
                created_at = CURRENT_TIMESTAMP";
                
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':store_id' => $storeId,
            ':access_token' => $accessToken,
            ':refresh_token' => $refreshToken
        ]);
    }
    
    private function getTokens($storeId) {
        $stmt = $this->db->prepare("SELECT * FROM tokens WHERE store_id = ?");
        $stmt->execute([$storeId]);
        return $stmt->fetch();
    }
}
