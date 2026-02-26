<?php

declare(strict_types=1);

use App\Http\Controllers\Settings\ApiTokenController;
use App\Http\Controllers\Settings\JobStatusController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\TwoFactorAuthenticationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth'])->group(function (): void {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('user-password.edit');

    Route::put('settings/password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::get('settings/appearance', fn () => Inertia::render('settings/Appearance'))->name('appearance.edit');

    Route::get('settings/two-factor', [TwoFactorAuthenticationController::class, 'show'])
        ->name('two-factor.show');

    Route::get('settings/api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index');
    Route::post('settings/api-tokens', [ApiTokenController::class, 'store'])->name('api-tokens.store');
    Route::delete('settings/api-tokens/{token}', [ApiTokenController::class, 'destroy'])->name('api-tokens.destroy');

    Route::get('settings/job-statuses', [JobStatusController::class, 'index'])->name('job-statuses.index');
    Route::post('settings/job-statuses', [JobStatusController::class, 'store'])->name('job-statuses.store');
    Route::post('settings/job-statuses/reorder', [JobStatusController::class, 'reorder'])->name('job-statuses.reorder');
    Route::patch('settings/job-statuses/{jobStatus}', [JobStatusController::class, 'update'])->name('job-statuses.update');
    Route::delete('settings/job-statuses/{jobStatus}', [JobStatusController::class, 'destroy'])->name('job-statuses.destroy');
});
