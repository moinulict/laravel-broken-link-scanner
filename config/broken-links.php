<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Start URL
    |--------------------------------------------------------------------------
    |
    | The URL to start scanning from. This should be your application's base URL.
    |
    */
    'start_url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Maximum Crawl Depth
    |--------------------------------------------------------------------------
    |
    | How many links deep should the crawler go? Set to -1 for unlimited.
    |
    */
    'max_depth' => env('BROKEN_LINKS_MAX_DEPTH', 5),

    /*
    |--------------------------------------------------------------------------
    | Concurrency
    |--------------------------------------------------------------------------
    |
    | How many URLs to check simultaneously.
    |
    */
    'concurrency' => env('BROKEN_LINKS_CONCURRENCY', 10),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | How long to wait for each request (in seconds).
    |
    */
    'timeout' => env('BROKEN_LINKS_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | User Agent
    |--------------------------------------------------------------------------
    |
    | The user agent string to use when making requests.
    |
    */
    'user_agent' => env('BROKEN_LINKS_USER_AGENT', 'Laravel Broken Link Scanner'),

    /*
    |--------------------------------------------------------------------------
    | Storage Table
    |--------------------------------------------------------------------------
    |
    | The database table to store broken links in.
    |
    */
    'storage_table' => 'broken_links',
];
