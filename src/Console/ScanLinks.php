<?php

namespace Moinul\LinkScanner\Console;

use Illuminate\Console\Command;
use Moinul\LinkScanner\Facades\LinkScanner;

class ScanLinks extends Command
{
    protected $signature = 'broken-links:scan 
                           {url? : Override start URL} 
                           {--depth= : Override max depth} 
                           {--clear-old : Truncate old results}';

    protected $description = 'Crawl your site and record any broken links (4xx/5xx).';

    public function handle(): int
    {
        if ($this->option('clear-old')) {
            \DB::table(config('broken-links.storage_table'))->truncate();
            $this->info('Old records cleared.');
        }

        $url   = $this->argument('url');
        $depth = $this->option('depth');

        if ($depth) {
            config(['broken-links.max_depth' => $depth]);
        }

        $this->info('Starting scan...');
        LinkScanner::scan($url);
        $this->info('Scan complete.');

        return Command::SUCCESS;
    }
}
