<?php
require_once __DIR__ . '/../src/Config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use EcwidApp\Api\EcwidApi;
use EcwidApp\Services\AuthService;
use EcwidApp\Services\BarcodeService;

session_start();

try {
    // Initialize services
    $authService = new AuthService();
    $api = new EcwidApi();
    
    // Only initialize BarcodeService if we're authenticated
    $barcodeService = null;
    if ($authService->isAuthenticated()) {
        try {
            $barcodeService = new BarcodeService($api);
        } catch (\Exception $e) {
            error_log("Failed to initialize BarcodeService: " . $e->getMessage());
        }
    }

    // Handle OAuth flow
    if (isset($_GET['code'])) {
        $authService->handleCallback($_GET['code']);
        header('Location: ' . APP_URL);
        exit;
    }

    // Check if authenticated
    if (!$authService->isAuthenticated()) {
        $loginUrl = $authService->getAuthUrl();
        header('Location: ' . $loginUrl);
        exit;
    }

    // Get store data
    $storeId = $_SESSION['store_id'];
    $accessToken = $_SESSION['access_token'];
    $api->setAccessToken($accessToken);
    $api->setStoreId($storeId);

    // Handle API requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        
        if (!$barcodeService) {
            http_response_code(503);
            echo json_encode(['error' => 'Barcode service unavailable']);
            exit;
        }
        
        switch ($_GET['action'] ?? '') {
            case 'generate':
                $products = $api->getProducts();
                $barcodes = $barcodeService->generateBarcodes($products);
                echo json_encode(['success' => true, 'barcodes' => $barcodes]);
                break;
                
            case 'update':
                if (!isset($_POST['product_id']) || !isset($_POST['barcode'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Missing required parameters']);
                    exit;
                }
                $productId = $_POST['product_id'];
                $barcode = $_POST['barcode'];
                $result = $api->updateProduct($productId, ['sku' => $barcode]);
                echo json_encode(['success' => true, 'result' => $result]);
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action']);
                exit;
        }
        exit;
    }

    // Render main app interface
    include __DIR__ . '/../templates/app.php';

} catch (\Exception $e) {
    error_log("Application error: " . $e->getMessage());
    
    if (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'error' => 'Internal server error',
            'message' => $e->getMessage()
        ]);
    } else {
        http_response_code(500);
        include __DIR__ . '/../templates/error.php';
    }
    exit;
}
