<?php

namespace Moinul\LinkScanner;

use Illuminate\Support\ServiceProvider;
use Moinul\LinkScanner\Console\ScanLinks;
use Moinul\LinkScanner\Services\LinkCrawler;

class LinkScannerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/broken-links.php' => config_path('broken-links.php'),
        ], 'config');

        // Publish migration
        if (! class_exists('CreateBrokenLinksTable')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/2025_04_18_000000_create_broken_links_table.php'
                    => database_path('migrations/2025_04_18_000000_create_broken_links_table.php'),
            ], 'migrations');
        }

        // Publish views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'linkscanner');
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/linkscanner'),
        ], 'views');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Register artisan command
        if ($this->app->runningInConsole()) {
            $this->commands([ScanLinks::class]);
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/broken-links.php',
            'broken-links'
        );

        $this->app->singleton('linkscanner', function ($app) {
            return new Services\LinkCrawler(
                $app['config']->get('broken-links')
            );
        });

        // Bind LinkCrawler with config
        $this->app->bind(LinkCrawler::class, function ($app) {
            return new LinkCrawler(
                $app['config']->get('broken-links')
            );
        });
    }
}
