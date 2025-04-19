<?php
namespace Moinul\LinkScanner\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Moinul\LinkScanner\LinkScannerServiceProvider;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            LinkScannerServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // 1) use sqlite in-memory
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');

        // 2) load & run our migration if table doesn't exist yet
        if (! Schema::hasTable(config('broken-links.storage_table'))) {
            require_once __DIR__ . '/../database/migrations/2025_04_18_000000_create_broken_links_table.php';
            (new \CreateBrokenLinksTable())->up();
        }
    }
}
