<?php

use Illuminate\Support\Facades\Route;
use Moinul\LinkScanner\Http\Controllers\BrokenLinkController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/broken-links', [BrokenLinkController::class, 'index'])->name('broken-links.index');
    Route::get('/broken-links/export', [BrokenLinkController::class, 'export'])->name('broken-links.export');
});

