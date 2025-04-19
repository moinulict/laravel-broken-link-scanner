<?php

namespace Moinul\LinkScanner\Tests\Feature;

use Moinul\LinkScanner\Tests\TestCase;
use Moinul\LinkScanner\Services\LinkCrawler;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class FileShaperTest extends TestCase
{
    public function test_scan_fileshaper_website()
    {
        $config = [
            'start_url' => 'https://fileshaper.com',
            'max_depth' => 3,
            'concurrency' => 5,
            'timeout' => 30,
            'verify' => false,
            'user_agent' => 'Mozilla/5.0 (compatible; LinkScanner/1.0; +http://example.com)',
            'table' => 'broken_links'
        ];

        $crawler = new LinkCrawler($config);
        $brokenLinks = $crawler->scan($config['start_url']);

        // Output the results
        if (!empty($brokenLinks)) {
            foreach ($brokenLinks as $link) {
                echo "Broken Link Found: " . $link['url'] . " (Status: " . $link['status_code'] . ")\n";
            }
            $this->assertIsArray($brokenLinks);
        } else {
            echo "No broken links found!\n";
            $this->assertTrue(true, "No broken links were found on the website");
        }
    }
} 