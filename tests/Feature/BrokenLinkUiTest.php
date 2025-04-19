<?php

namespace Moinul\LinkScanner\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Moinul\LinkScanner\Models\BrokenLink;
use Moinul\LinkScanner\Tests\TestCase;

class BrokenLinkUiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create test routes
        Route::middleware('web')->group(function () {
            Route::get('/broken-links', function () {
                $links = BrokenLink::latest()->paginate(25);
                return view('linkscanner::broken-links.index', compact('links'));
            });

            Route::get('/broken-links/export', function () {
                return response()->download(
                    storage_path('app/broken-links.xlsx'),
                    'broken-links.xlsx',
                    ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
                );
            });
        });
    }

    public function test_index_shows_links()
    {
        BrokenLink::create([
            'url' => 'http://example.com/broken',
            'status_code' => 404,
            'reason' => 'Not Found',
            'checked_at' => now(),
        ]);

        $response = $this->get('/broken-links');

        $response->assertStatus(200)
                ->assertSee('Broken Links Report')
                ->assertSee('http://example.com/broken');
    }

    public function test_export_returns_excel()
    {
        BrokenLink::create([
            'url' => 'http://example.com/broken',
            'status_code' => 404,
            'reason' => 'Not Found',
            'checked_at' => now(),
        ]);

        // Create a dummy Excel file for testing
        file_put_contents(
            storage_path('app/broken-links.xlsx'),
            'dummy content'
        );

        $response = $this->get('/broken-links/export');

        $response->assertStatus(200)
                ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // Clean up
        @unlink(storage_path('app/broken-links.xlsx'));
    }
}
