<?php

namespace Moinul\LinkScanner\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DomCrawler\Crawler;
use Moinul\LinkScanner\Models\BrokenLink;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class LinkCrawler
{
    protected array $config;
    protected Client $client;
    protected ?Command $command = null;
    protected array $visitedUrls = [];
    protected string $baseDomain;
    protected int $currentDepth = 0;

    public function __construct(array $config, ?Client $client = null)
    {
        $this->config = $config;
        $this->client = $client ?? new Client([
            'timeout'      => $config['timeout'],
            'headers'      => ['User-Agent' => $config['user_agent']],
            'http_errors'  => false,
            'verify'       => false, // Skip SSL verification
        ]);
    }

    public function setCommand(Command $command): self
    {
        $this->command = $command;
        return $this;
    }

    public function scan(string $url = null): void
    {
        $startUrl = $url ?: $this->config['start_url'];
        $this->baseDomain = parse_url($startUrl, PHP_URL_HOST);
        $this->visitedUrls = [];
        $this->currentDepth = 0;

        $urlsToVisit = [$startUrl];

        while (!empty($urlsToVisit) && $this->currentDepth < $this->config['max_depth']) {
            $currentUrl = array_shift($urlsToVisit);
            
            if (in_array($currentUrl, $this->visitedUrls)) {
                continue;
            }

            $this->visitedUrls[] = $currentUrl;
            $newUrls = $this->crawlPage($currentUrl);
            
            // Add new URLs to the queue
            foreach ($newUrls as $newUrl) {
                if (!in_array($newUrl, $this->visitedUrls) && !in_array($newUrl, $urlsToVisit)) {
                    $urlsToVisit[] = $newUrl;
                }
            }

            $this->currentDepth++;
        }

        if ($this->command) {
            $this->command->info('Scan completed. Total pages crawled: ' . count($this->visitedUrls));
        }
    }

    protected function crawlPage(string $url): array
    {
        $newUrls = [];

        try {
            if ($this->command) {
                $this->command->info("Crawling page: $url");
            }

            // Get the page content
            $response = $this->client->get($url);
            $html = (string) $response->getBody();
            
            // Parse all links
            $crawler = new Crawler($html, $url);
            $crawler->filter('a')->each(function (Crawler $node) use ($url, &$newUrls) {
                $href = $node->attr('href') ?? '';
                $text = trim($node->text());
                
                // Check for empty or hash-only links
                if (empty($href) || $href === '#' || preg_match('/^#.+$/', $href)) {
                    $this->logBrokenLink($href, 0, 'Empty or hash-only link', $text);
                    return;
                }
                
                try {
                    // Convert relative to absolute URL
                    $absoluteUrl = $this->makeAbsolute($href, $url);
                    if (!$absoluteUrl) {
                        $this->logBrokenLink($href, 0, 'Invalid URL format', $text);
                        return;
                    }
                    
                    // Check if URL is accessible
                    $this->checkUrl($absoluteUrl, $text);
                    
                    // If URL is from the same domain, add it to crawl queue
                    if ($this->shouldCrawl($absoluteUrl)) {
                        $newUrls[] = $absoluteUrl;
                    }
                    
                } catch (\Exception $e) {
                    $this->logBrokenLink($href, 0, $e->getMessage(), $text);
                }
            });
            
        } catch (\Exception $e) {
            if ($this->command) {
                $this->command->error('Failed to fetch page: ' . $e->getMessage());
            }
        }

        return $newUrls;
    }

    protected function shouldCrawl(string $url): bool
    {
        $urlDomain = parse_url($url, PHP_URL_HOST);
        return $urlDomain === $this->baseDomain;
    }

    protected function checkUrl(string $url, string $linkText): void
    {
        if ($this->command && $this->command->getOutput()->isVerbose()) {
            $this->command->line("Checking: $url");
        }

        try {
            $response = $this->client->get($url);
            $status = $response->getStatusCode();
            
            if ($status >= 400) {
                $this->logBrokenLink($url, $status, $response->getReasonPhrase(), $linkText);
            }
        } catch (RequestException $e) {
            $status = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
            $reason = $e->hasResponse() ? $e->getResponse()->getReasonPhrase() : $e->getMessage();
            $this->logBrokenLink($url, $status, $reason, $linkText);
        } catch (\Exception $e) {
            $this->logBrokenLink($url, 0, $e->getMessage(), $linkText);
        }
    }

    protected function makeAbsolute(string $href, string $baseUrl): ?string
    {
        // Handle mailto:, tel:, javascript: links
        if (preg_match('/^(mailto:|tel:|javascript:)/', $href)) {
            return null;
        }

        try {
            return (string) \GuzzleHttp\Psr7\UriResolver::resolve(
                new \GuzzleHttp\Psr7\Uri($baseUrl),
                new \GuzzleHttp\Psr7\Uri($href)
            );
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function logBrokenLink(string $url, int $status, string $reason, string $linkText): void
    {
        if ($this->command && $this->command->getOutput()->isVerbose()) {
            $this->command->error(sprintf(
                'Broken Link Found - URL: %s, Status: %d, Reason: %s, Text: %s',
                $url,
                $status,
                $reason,
                $linkText
            ));
        }

        BrokenLink::create([
            'url'         => $url,
            'status_code' => $status,
            'reason'      => $reason,
            'link_text'   => $linkText,
            'checked_at'  => now(),
        ]);
    }
}
