<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('tv-monitor-welcome');
});

// Guide Routes
Route::get('/guides', [App\Http\Controllers\GuideController::class, 'index'])->name('guides.index');
Route::get('/guides/{slug}', [App\Http\Controllers\GuideController::class, 'show'])->name('guides.show');

// Add route for clearing error log entries
Route::post('/admin/clear-error-log-entry', [App\Http\Controllers\ErrorLogController::class, 'clearEntry'])
    ->middleware(['web', 'auth'])
    ->name('clear-error-log-entry');

// FFmpeg Stream Management
Route::get('/api/streams/output', [App\Http\Controllers\FFmpegController::class, 'listOutputStreams'])
    ->middleware(['web', 'auth'])
    ->name('api.streams.output.list');

// Public stream viewing routes (no auth required)
Route::get('/view/multiview/{id}', [App\Http\Controllers\StreamViewController::class, 'viewMultiview'])
    ->name('view.multiview');
Route::get('/view/streams', [App\Http\Controllers\StreamViewController::class, 'listViewableStreams'])
    ->name('view.streams');
Route::get('/api/view/multiview-status/{id}', [App\Http\Controllers\StreamViewController::class, 'checkMultiviewStatus'])
    ->name('api.view.multiview.status');

// Stream proxy routes (for handling 403 errors)
Route::get('/stream-proxy/{streamId}/playlist.m3u8', [App\Http\Controllers\StreamProxyController::class, 'proxyPlaylist'])
    ->name('stream.proxy.playlist');

// Handle various segment formats
Route::get('/stream-proxy/{streamId}/segment_{segmentNumber}.ts', [App\Http\Controllers\StreamProxyController::class, 'proxySegment'])
    ->where('segmentNumber', '[0-9]+') // Capture segment_123.ts format
    ->name('stream.proxy.segment.format1');

Route::get('/stream-proxy/{streamId}/segment{segmentNumber}.ts', [App\Http\Controllers\StreamProxyController::class, 'proxySegment'])
    ->where('segmentNumber', '[0-9]+') // Capture segment123.ts format
    ->name('stream.proxy.segment.format2');

Route::get('/stream-proxy/{streamId}/{segmentNumber}.ts', [App\Http\Controllers\StreamProxyController::class, 'proxySegment'])
    ->where('segmentNumber', '[0-9]+') // Capture 123.ts format
    ->name('stream.proxy.segment.format3');

// Legacy segment route
Route::get('/stream-proxy/{streamId}/segment/{segmentId}', [App\Http\Controllers\StreamProxyController::class, 'proxySegment'])
    ->where('segmentId', '.*') // Allow any format including segment_123.ts
    ->name('stream.proxy.segment');

// Add empty segment handler
Route::get('/stream-proxy/{streamId}/empty.ts', [App\Http\Controllers\StreamProxyController::class, 'emptySegment'])
    ->name('stream.proxy.empty');

// DASH proxy routes
Route::get('/stream-proxy/{streamId}/manifest.mpd', [App\Http\Controllers\StreamProxyController::class, 'proxyDashManifest'])
    ->name('stream.proxy.dash.manifest');
Route::get('/stream-proxy/{streamId}/dash/{segmentPath}', [App\Http\Controllers\StreamProxyController::class, 'proxyDashSegment'])
    ->where('segmentPath', '.*')
    ->name('stream.proxy.dash.segment');

// Dashboard route (requires authentication)
Route::get('/view/multiview/{id}/dashboard', [App\Http\Controllers\StreamViewController::class, 'dashboard'])
    ->middleware(['web', 'auth'])
    ->name('view.multiview.dashboard');
