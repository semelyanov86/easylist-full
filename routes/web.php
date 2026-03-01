<?php

declare(strict_types=1);

use App\Http\Controllers\JobController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', fn () => Inertia::render('Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
]))->name('home');

Route::get('dashboard', fn () => Inertia::render('Dashboard'))->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('jobs', [JobController::class, 'index'])->name('jobs.index');
    Route::post('jobs', [JobController::class, 'store'])->name('jobs.store');
    Route::patch('jobs/{job}/move', [JobController::class, 'move'])->name('jobs.move');
    Route::patch('jobs/{job}/favorite', [JobController::class, 'toggleFavorite'])->name('jobs.toggle-favorite');
    Route::delete('jobs/{job}', [JobController::class, 'destroy'])->name('jobs.destroy');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/webauthn.php';
