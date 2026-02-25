<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\WebAuthnChallengeController;
use App\Http\Controllers\Auth\WebAuthnRegisterController;
use Illuminate\Support\Facades\Route;

// Регистрация ключей (только для аутентифицированных пользователей)
Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::post('webauthn/register/challenge', [WebAuthnRegisterController::class, 'challenge'])
        ->name('webauthn.register.challenge');

    Route::post('webauthn/register', [WebAuthnRegisterController::class, 'store'])
        ->name('webauthn.register');

    Route::delete('webauthn/{credentialId}', [WebAuthnRegisterController::class, 'destroy'])
        ->name('webauthn.destroy');
});

// 2FA challenge (web middleware, без auth — login flow)
Route::middleware(['web'])->group(function (): void {
    Route::post('webauthn/auth/challenge', [WebAuthnChallengeController::class, 'challenge'])
        ->name('webauthn.auth.challenge');

    Route::post('webauthn/auth/verify', [WebAuthnChallengeController::class, 'verify'])
        ->name('webauthn.auth.verify');
});
