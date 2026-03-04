<?php

declare(strict_types=1);

use App\Http\Controllers\AiCompanyAnalyzerController;
use App\Http\Controllers\AiExtractJobTagsController;
use App\Http\Controllers\AiFormatController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\JobCommentController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\JobDocumentController;
use App\Http\Controllers\JobTaskController;
use App\Http\Controllers\SkillController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', fn () => Inertia::render('Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
]))->name('home');

Route::get('dashboard', fn () => Inertia::render('Dashboard'))->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('jobs', [JobController::class, 'index'])->name('jobs.index');
    Route::get('jobs/{job}', [JobController::class, 'show'])->name('jobs.show');
    Route::post('jobs', [JobController::class, 'store'])->name('jobs.store');
    Route::patch('jobs/{job}', [JobController::class, 'update'])->name('jobs.update');
    Route::patch('jobs/{job}/move', [JobController::class, 'move'])->name('jobs.move');
    Route::patch('jobs/{job}/favorite', [JobController::class, 'toggleFavorite'])->name('jobs.toggle-favorite');
    Route::delete('jobs/{job}', [JobController::class, 'destroy'])->name('jobs.destroy');

    Route::post('jobs/{job}/comments', [JobCommentController::class, 'store'])->name('job-comments.store');
    Route::patch('job-comments/{comment}', [JobCommentController::class, 'update'])->name('job-comments.update');
    Route::delete('job-comments/{comment}', [JobCommentController::class, 'destroy'])->name('job-comments.destroy');

    Route::post('jobs/{job}/documents', [JobDocumentController::class, 'store'])->name('job-documents.store');
    Route::get('job-documents/{document}/download', [JobDocumentController::class, 'download'])->name('job-documents.download');
    Route::delete('job-documents/{document}', [JobDocumentController::class, 'destroy'])->name('job-documents.destroy');

    Route::post('jobs/{job}/contacts', [ContactController::class, 'store'])->name('contacts.store');
    Route::patch('contacts/{contact}', [ContactController::class, 'update'])->name('contacts.update');
    Route::delete('contacts/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');

    Route::post('jobs/{job}/tasks', [JobTaskController::class, 'store'])->name('job-tasks.store');
    Route::patch('job-tasks/{jobTask}', [JobTaskController::class, 'update'])->name('job-tasks.update');
    Route::patch('job-tasks/{jobTask}/toggle', [JobTaskController::class, 'toggle'])->name('job-tasks.toggle');
    Route::delete('job-tasks/{jobTask}', [JobTaskController::class, 'destroy'])->name('job-tasks.destroy');

    Route::get('skills/search', [SkillController::class, 'search'])->name('skills.search');
    Route::post('skills', [SkillController::class, 'store'])->name('skills.store');

    Route::post('ai/format-text', AiFormatController::class)->name('ai.format-text');
    Route::post('ai/extract-job-tags/{job}', AiExtractJobTagsController::class)->name('ai.extract-job-tags');
    Route::post('ai/company-analysis/{job}', AiCompanyAnalyzerController::class)->name('ai.company-analysis');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/webauthn.php';
