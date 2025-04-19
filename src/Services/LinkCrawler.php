<?php

namespace Moinul\LinkScanner\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\EachPromise;
use Symfony\Component\DomCrawler\Crawler;
use Moinul\LinkScanner\Models\BrokenLink;

class LinkCrawler
{
    protected array $config;
    protected Client $client;
    protected array $seen = [];

    public function __construct(array $config, ?Client $client = null)
    {
        $this->config = $config;
        $this->client = $client ?? new Client([
            'timeout'      => $config['timeout'],
            'headers'      => ['User-Agent' => $config['user_agent']],
            'http_errors'  => false,
        ]);
    }

    public function scan(string $startUrl = null): void
    {
        $startUrl = $startUrl ?: $this->config['start_url'];
        $this->crawl([$startUrl], 0);
    }

    protected function crawl(array $urls, int $depth): void
    {
        if ($depth > $this->config['max_depth']) {
            return;
        }

        $promises = [];
        foreach ($urls as $url) {
            if (isset($this->seen[$url])) {
                continue;
            }
            $this->seen[$url] = true;
            $promises[] = $this->client->getAsync($url)
                ->then(fn($response) => $this->handleResponse($url, $response));
        }

        $each = new EachPromise($promises, [
            'concurrency' => $this->config['concurrency'],
        ]);
        $each->promise()->wait();

        // collect new URLs
        $newUrls = [];
        foreach ($this->seen as $u => $_) {
            if ($_ === true) {
                $newUrls[] = $u;
                $this->seen[$u] = false;
            }
        }

        if ($newUrls) {
            $this->crawl($newUrls, $depth + 1);
        }
    }

    protected function handleResponse(string $url, $response): void
    {
        $status = $response->getStatusCode();
        if ($status >= 400) {
            BrokenLink::create([
                'url'         => $url,
                'status_code' => $status,
                'reason'      => $response->getReasonPhrase(),
                'checked_at'  => now(),
            ]);
            return;
        }

        $html = (string) $response->getBody();
        $crawler = new Crawler($html);
        $crawler->filter('a[href]')->each(function (Crawler $node) use (&$newUrls) {
            $link = $node->attr('href');
            $abs   = \GuzzleHttp\Psr7\UriResolver::resolve(
                new \GuzzleHttp\Psr7\Uri($this->config['start_url']),
                new \GuzzleHttp\Psr7\Uri($link)
            );
            $hostMatch = parse_url((string)$abs, PHP_URL_HOST) === parse_url($this->config['start_url'], PHP_URL_HOST);
            if ($hostMatch) {
                $newUrls[] = (string) $abs;
            }
        });
    }
}
