# EAN Barcode Generator for Ecwid

A native Ecwid app for generating and managing EAN-13 barcodes for your products.

## Features

- Automatic EAN-13 barcode generation
- Batch processing for multiple products
- Secure OAuth2 authentication
- Integrated with Ecwid Control Panel
- Real-time barcode validation
- Persistent barcode tracking

## Installation

1. Register your app at https://my.ecwid.com/cp/#develop-apps
   - Set OAuth Redirect URI to: `https://your-domain.com/ecwid-barcode-app/public/`
   - Request the following scopes:
     * read_catalog
     * update_catalog
     * read_store_profile

2. Configure your server:
   ```bash
   # Clone the repository
   git clone https://github.com/your-username/ecwid-barcode-app.git
   cd ecwid-barcode-app

   # Install dependencies
   composer install --no-dev

   # Set up database
   mysql -u your-user -p your-database < schema/install.sql
   ```

3. Update configuration:
   Edit `src/Config/config.php`:
   ```php
   define('APP_URL', 'https://your-domain.com/ecwid-barcode-app/public');
   define('CLIENT_ID', 'your-client-id');
   define('CLIENT_SECRET', 'your-client-secret');
   ```

4. Set up your database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'your_database');
   define('DB_USER', 'your_user');
   define('DB_PASS', 'your_password');
   ```

## Usage

1. Install the app from Ecwid App Market
2. Navigate to your Ecwid Control Panel
3. Find "EAN Barcode Generator" in the Apps section
4. Click "Generate Barcodes" to process all products
5. Or use individual "Generate" buttons for specific products

## Development

### Local Development Setup

1. Install PHP 7.4 or higher
2. Install Composer
3. Set up a local MySQL database
4. Configure your web server (Apache/Nginx)
5. Install dependencies:
   ```bash
   composer install
   ```

### Testing

1. Configure test environment:
   ```bash
   cp src/Config/config.php.example src/Config/config.php
   # Edit config.php with your test credentials
   ```

2. Run the development server:
   ```bash
   cd public
   php -S localhost:8000
   ```

## Deployment

1. Update version in `composer.json`
2. Build production assets:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
3. Upload to your server
4. Update database schema if needed

## Support

For support, please contact support@thaxam.no

## License

This project is licensed under the MIT License - see the LICENSE file for details.
