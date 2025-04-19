<?php

namespace Moinul\LinkScanner\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Moinul\LinkScanner\Facades\LinkScanner;
use Moinul\LinkScanner\Models\BrokenLink;
use Moinul\LinkScanner\Services\LinkCrawler;

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
            DB::table(config('broken-links.storage_table'))->truncate();
            $this->info('Old records cleared.');
        }

        $url = $this->argument('url');
        $depth = $this->option('depth');

        if ($depth) {
            config(['broken-links.max_depth' => $depth]);
        }

        $this->info('Starting scan...');
        $this->info('URL: ' . ($url ?: config('broken-links.start_url')));
        $this->info('Max Depth: ' . config('broken-links.max_depth'));
        
        // Store initial count
        $initialCount = BrokenLink::count();
        
        // Get the crawler instance and set the command for verbose output
        $crawler = app(LinkCrawler::class);
        $crawler->setCommand($this);
        
        // Start the scan
        $crawler->scan($url);
        
        // Get final count and new broken links
        $finalCount = BrokenLink::count();
        $newBrokenLinks = BrokenLink::latest()
            ->take($finalCount - $initialCount)
            ->get();

        $this->info('Scan complete.');
        
        if ($newBrokenLinks->isEmpty()) {
            $this->info('No broken links found! 🎉');
        } else {
            $this->error(sprintf('Found %d broken links:', $newBrokenLinks->count()));
            
            // Create table headers
            $headers = ['URL', 'Status', 'Reason'];
            $rows = [];
            
            foreach ($newBrokenLinks as $link) {
                $rows[] = [
                    $link->url,
                    $link->status_code,
                    $link->reason
                ];
            }
            
            $this->table($headers, $rows);
            
            $this->info('To view full report, visit: /broken-links');
            $this->info('To export results, visit: /broken-links/export');
        }

        return Command::SUCCESS;
    }
}
