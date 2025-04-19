<!-- README.md -->
# Laravel Broken Link Scanner

A Laravel package to scan and report dead or broken links in your Laravel application. It crawls your website and identifies any broken links (4xx/5xx status codes).

## Features

- Asynchronous crawling with configurable concurrency
- Configurable crawl depth
- Export results to Excel
- Web interface to view and manage broken links
- Detailed progress reporting
- Database storage of broken links
- Support for Laravel 8.x, 9.x, 10.x, and 11.x

## Installation

You can install the package via composer:

```bash
composer require moinul/laravel-broken-link-scanner
```

## Configuration

1. Publish the config file:

```bash
php artisan vendor:publish --provider="Moinul\LinkScanner\LinkScannerServiceProvider" --tag="config"
```

2. Publish and run the migrations:

```bash
php artisan vendor:publish --provider="Moinul\LinkScanner\LinkScannerServiceProvider" --tag="migrations"
php artisan migrate
```

3. (Optional) Publish the views if you want to customize them:

```bash
php artisan vendor:publish --provider="Moinul\LinkScanner\LinkScannerServiceProvider" --tag="views"
```

## Usage

### Command Line

Scan your site for broken links:

```bash
# Scan using config start_url
php artisan broken-links:scan

# Scan specific URL
php artisan broken-links:scan https://example.com

# Scan with custom depth
php artisan broken-links:scan --depth=3

# Clear old results before scanning
php artisan broken-links:scan --clear-old

# Show detailed progress
php artisan broken-links:scan -v

# Show very detailed progress
php artisan broken-links:scan -vv

# Show debug level information
php artisan broken-links:scan -vvv
```

### Web Interface

The package provides a web interface at `/broken-links` to:
- View all broken links
- See status codes and reasons
- Export results to Excel
- Track when links were last checked

### Configuration

You can configure the package by editing the `config/broken-links.php` file:

```php
return [
    // The URL to start scanning from
    'start_url' => env('APP_URL', 'http://localhost'),

    // How many links deep should the crawler go? Set to -1 for unlimited
    'max_depth' => env('BROKEN_LINKS_MAX_DEPTH', 5),

    // How many URLs to check simultaneously
    'concurrency' => env('BROKEN_LINKS_CONCURRENCY', 10),

    // How long to wait for each request (in seconds)
    'timeout' => env('BROKEN_LINKS_TIMEOUT', 10),

    // The user agent string to use when making requests
    'user_agent' => env('BROKEN_LINKS_USER_AGENT', 'Laravel Broken Link Scanner'),

    // The database table to store broken links in
    'storage_table' => 'broken_links',
];
```

### Programmatic Usage

You can also use the facade to scan programmatically:

```php
use Moinul\LinkScanner\Facades\LinkScanner;

// Scan using config start_url
LinkScanner::scan();

// Scan specific URL
LinkScanner::scan('https://example.com');
```

### Events

The package dispatches the following events:

- `BrokenLinkFound`: When a broken link is discovered
- `ScanCompleted`: When the scan is complete

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email engrminul@gmail.com instead of using the issue tracker.

## Credits

- [Moinul Islam](https://github.com/moinulict)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
