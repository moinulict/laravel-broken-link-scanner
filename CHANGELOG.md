# Changelog

All notable changes to `laravel-broken-link-scanner` will be documented in this file.

## 1.1.1 - 2024-03-XX

### Changed
- Updated dependencies for better Laravel 11 and PHP 8.4 support
- Updated maatwebsite/excel to ^3.1.50
- Added support for Symfony 7.0 components
- Updated dev dependencies (testbench, phpunit, mockery)

## 1.1.0 - 2024-03-XX

### Added
- Detailed progress reporting with verbosity levels (-v, -vv, -vvv)
- Real-time status updates during scanning
- Improved command output with tables and emojis
- Support for Laravel 11.x

### Changed
- Updated command syntax to use argument for URL instead of option
- Improved error handling and reporting
- Enhanced documentation with more examples
- Better progress tracking during scans

### Fixed
- Fixed dependency injection in LinkCrawler
- Corrected namespace issues
- Fixed verbose output conflicts

## 1.0.0 - 2024-03-XX

### Added
- Initial release
- Basic broken link scanning functionality
- Web interface for viewing results
- Excel export capability
- Configurable crawl depth and concurrency
- Database storage for broken links 