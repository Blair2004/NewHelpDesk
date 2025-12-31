<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\OAuthController;
use Illuminate\Support\Facades\Route;

// OAuth routes
Route::get('/oauth/redirect', [OAuthController::class, 'redirect'])->name('oauth.redirect');
Route::get('/oauth/callback', [OAuthController::class, 'callback'])->name('oauth.callback');
Route::post('/logout', [OAuthController::class, 'logout'])->name('logout');

// SPA catch-all
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
