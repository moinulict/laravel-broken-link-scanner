<?php

namespace Moinul\LinkScanner\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DomCrawler\Crawler;
use Moinul\LinkScanner\Models\BrokenLink;
use Illuminate\Console\Command;

class LinkCrawler
{
    protected array $config;
    protected Client $client;
    protected ?Command $command = null;

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
        $url = $url ?: $this->config['start_url'];
        
        try {
            // Get the page content
            $response = $this->client->get($url);
            $html = (string) $response->getBody();
            
            // Parse all links
            $crawler = new Crawler($html, $url);
            $links = $crawler->filter('a')->each(function (Crawler $node) use ($url) {
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
                    
                } catch (\Exception $e) {
                    $this->logBrokenLink($href, 0, $e->getMessage(), $text);
                }
            });
            
            if ($this->command) {
                $this->command->info('Scan completed.');
            }
            
        } catch (\Exception $e) {
            if ($this->command) {
                $this->command->error('Failed to fetch page: ' . $e->getMessage());
            }
        }
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
