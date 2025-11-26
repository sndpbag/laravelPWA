<?php

use Illuminate\Support\Facades\Route;
use Sndpbag\Sndppwa\Http\Controllers\PwaController;
use Sndpbag\Sndppwa\Http\Controllers\WebAuthnController;

// PWA Core Routes
Route::get('/manifest.json', [PwaController::class, 'manifest'])
    ->name('pwa.manifest');

Route::get('/sw.js', [PwaController::class, 'serviceWorker'])
    ->name('pwa.serviceworker');

Route::get('/offline', [PwaController::class, 'offline'])
    ->name('pwa.offline');

// TWA Asset Links
Route::get('/.well-known/assetlinks.json', [PwaController::class, 'assetLinks'])
    ->name('pwa.assetlinks');

// WebAuthn Routes (Biometric Authentication)
Route::prefix('pwa/webauthn')->name('pwa.webauthn.')->group(function () {
    Route::post('/register/options', [WebAuthnController::class, 'registerOptions'])
        ->name('register.options');
    
    Route::post('/register/verify', [WebAuthnController::class, 'registerVerify'])
        ->name('register.verify');
    
    Route::post('/login/options', [WebAuthnController::class, 'loginOptions'])
        ->name('login.options');
    
    Route::post('/login/verify', [WebAuthnController::class, 'loginVerify'])
        ->name('login.verify');
});