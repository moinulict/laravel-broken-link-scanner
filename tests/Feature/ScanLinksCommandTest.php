<?php

namespace Moinul\LinkScanner\Tests\Feature;

use Moinul\LinkScanner\Models\BrokenLink;
use Moinul\LinkScanner\Tests\TestCase;

class ScanLinksCommandTest extends TestCase
{
    public function test_command_clears_and_scans()
    {
        // seed an old record
        BrokenLink::create([
            'url' => 'http://old.link',
            'status_code' => 404,
            'reason' => 'Not Found',
            'checked_at' => now(),
        ]);

        $this->artisan('broken-links:scan', ['--clear-old' => true])
             ->expectsOutput('Old records cleared.')
             ->expectsOutput('Starting scan...')
             ->expectsOutput('Scan complete.')
             ->assertExitCode(0);

        // since no real HTTP calls were made, table is empty
        $this->assertDatabaseCount('broken_links', 0);
    }
}
