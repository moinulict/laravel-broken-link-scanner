<?php

namespace Moinul\LinkScanner\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Moinul\LinkScanner\Models\BrokenLink;
use Moinul\LinkScanner\Services\LinkCrawler;
use Moinul\LinkScanner\Tests\TestCase;

class LinkCrawlerTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_records_broken_link()
    {
        $response = new Response(500, [], '<html></html>', '1.1', 'Internal Server Error');
        $promise = new FulfilledPromise($response);

        $mock = Mockery::mock(Client::class);
        $mock->shouldReceive('getAsync')
             ->once()
             ->with('http://example.com/nonexistent')
             ->andReturn($promise);

        $config = [
            'start_url' => 'http://example.com',
            'max_depth' => 1,
            'concurrency' => 1,
            'timeout' => 5,
            'user_agent' => 'Test',
            'storage_table' => 'broken_links'
        ];

        $crawler = new LinkCrawler($config, $mock);
        $crawler->scan('http://example.com/nonexistent');

        $this->assertDatabaseHas('broken_links', [
            'url' => 'http://example.com/nonexistent',
            'status_code' => 500,
            'reason' => 'Internal Server Error',
        ]);
    }
}
