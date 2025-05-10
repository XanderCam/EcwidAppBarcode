<?php
// App Configuration
define('APP_URL', 'https://your-domain.com/ecwid-barcode-app/public');
define('CLIENT_ID', 'your-client-id');
define('CLIENT_SECRET', 'your-client-secret');

// Ecwid API Configuration
define('ECWID_API_URL', 'https://app.ecwid.com/api/v3');
define('ECWID_OAUTH_URL', 'https://my.ecwid.com/api/oauth');

// Required API Scopes
define('API_SCOPES', [
    'read_catalog',
    'update_catalog',
    'read_store_profile'
]);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'ecwid_barcode_app');
define('DB_USER', 'your-db-user');
define('DB_PASS', 'your-db-password');

// App Settings
define('APP_NAME', 'EAN Barcode Generator');
define('APP_VERSION', '1.0.0');
define('APP_DESCRIPTION', 'Generate and manage EAN barcodes for your Ecwid products');

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
