<!-- README.md -->
# Laravel Broken Link Scanner

A Laravel package to scan and report dead or broken links in your Laravel application.

## Installation

You can install the package via composer:

```bash
composer require moinul/laravel-broken-link-scanner
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --provider="Moinul\LinkScanner\LinkScannerServiceProvider" --tag="config"
```

Publish and run the migrations:

```bash
php artisan vendor:publish --provider="Moinul\LinkScanner\LinkScannerServiceProvider" --tag="migrations"
php artisan migrate
```

## Usage

### Command Line

Scan your site for broken links:

```bash
php artisan broken-links:scan
```

Options:
- `--url=http://example.com` - Override start URL
- `--depth=5` - Override max depth
- `--clear-old` - Clear old results before scanning

### Web Interface

The package provides a web interface at `/broken-links` to view and export broken links.

### Configuration

You can configure the package by editing the `config/broken-links.php` file:

```php
return [
    'start_url' => env('APP_URL', 'http://localhost'),
    'max_depth' => env('BROKEN_LINKS_MAX_DEPTH', 5),
    'concurrency' => env('BROKEN_LINKS_CONCURRENCY', 10),
    'timeout' => env('BROKEN_LINKS_TIMEOUT', 10),
    'user_agent' => env('BROKEN_LINKS_USER_AGENT', 'Laravel Broken Link Scanner'),
];
```

### Facade Usage

You can also use the facade to scan programmatically:

```php
use Moinul\LinkScanner\Facades\LinkScanner;

LinkScanner::scan(); // Scan from config start_url
LinkScanner::scan('http://example.com'); // Scan from specific URL
```

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
