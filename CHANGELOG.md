# Changelog

## [0.1.0] - 2024-01-09

### Added
- Initial conversion of WordPress plugin to native Ecwid app
- Core application structure and components:
  - OAuth2 authentication flow
  - Ecwid API integration service
  - Barcode generation service
  - Database schema for tokens and settings
  - Error handling and logging
  - Frontend UI with Tailwind CSS
- Features:
  - EAN-13 barcode generation
  - Batch processing capability
  - Product SKU management
  - Secure token storage
  - Real-time barcode validation

### Changed
- Moved from WordPress hooks to direct Ecwid API calls
- Replaced WordPress admin interface with Ecwid Control Panel integration
- Updated authentication to use Ecwid OAuth instead of WordPress admin

### Technical Details
- PHP 7.4+ compatibility
- PDO MySQL for database operations
- Composer for dependency management
- PSR-4 autoloading
- Tailwind CSS for styling
- Ecwid JS SDK integration
