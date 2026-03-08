<?php

declare(strict_types=1);

use App\Http\Controllers\AiCompanyAnalyzerController;
use App\Http\Controllers\AiContactFinderController;
use App\Http\Controllers\AiCoverLetterController;
use App\Http\Controllers\AiExtractJobTagsController;
use App\Http\Controllers\AiFormatController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JobCommentController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\JobDocumentController;
use App\Http\Controllers\JobPublicViewController;
use App\Http\Controllers\JobTaskController;
use App\Http\Controllers\Shopping\FolderController;
use App\Http\Controllers\Shopping\ShoppingController;
use App\Http\Controllers\Shopping\ShoppingItemController;
use App\Http\Controllers\Shopping\ShoppingListController;
use App\Http\Controllers\SkillController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', fn () => Inertia::render('Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
]))->name('home');

Route::get('dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::get('job/view/{uuid}', JobPublicViewController::class)->name('jobs.public-view');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('jobs', [JobController::class, 'index'])->name('jobs.index');
    Route::get('jobs/{job}', [JobController::class, 'show'])->name('jobs.show');
    Route::post('jobs', [JobController::class, 'store'])->name('jobs.store');
    Route::patch('jobs/{job}', [JobController::class, 'update'])->name('jobs.update');
    Route::patch('jobs/{job}/move', [JobController::class, 'move'])->name('jobs.move');
    Route::patch('jobs/{job}/favorite', [JobController::class, 'toggleFavorite'])->name('jobs.toggle-favorite');
    Route::post('jobs/{job}/share', [JobController::class, 'share'])->name('jobs.share');
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
    Route::post('ai/find-contacts/{job}', AiContactFinderController::class)->name('ai.find-contacts');
    Route::post('ai/cover-letter/{job}', AiCoverLetterController::class)->name('ai.cover-letter');

    // Списки покупок
    Route::get('shopping', [ShoppingController::class, 'index'])->name('shopping.index');

    Route::post('shopping/lists', [ShoppingListController::class, 'store'])->name('shopping.lists.store');
    Route::patch('shopping/lists/{shoppingList}', [ShoppingListController::class, 'update'])->name('shopping.lists.update');
    Route::delete('shopping/lists/{shoppingList}', [ShoppingListController::class, 'destroy'])->name('shopping.lists.destroy');
    Route::post('shopping/lists/reorder', [ShoppingListController::class, 'reorder'])->name('shopping.lists.reorder');

    Route::post('shopping/items', [ShoppingItemController::class, 'store'])->name('shopping.items.store');
    Route::patch('shopping/items/{shoppingItem}', [ShoppingItemController::class, 'update'])->name('shopping.items.update');
    Route::patch('shopping/items/{shoppingItem}/toggle', [ShoppingItemController::class, 'toggleDone'])->name('shopping.items.toggle');
    Route::delete('shopping/items/{shoppingItem}', [ShoppingItemController::class, 'destroy'])->name('shopping.items.destroy');
    Route::post('shopping/items/reorder', [ShoppingItemController::class, 'reorder'])->name('shopping.items.reorder');
    Route::patch('shopping/lists/{shoppingList}/uncross', [ShoppingItemController::class, 'uncrossAll'])->name('shopping.items.uncross-all');
    Route::delete('shopping/lists/{shoppingList}/items', [ShoppingItemController::class, 'destroyAll'])->name('shopping.items.destroy-all');

    Route::post('shopping/folders', [FolderController::class, 'store'])->name('shopping.folders.store');
    Route::patch('shopping/folders/{folder}', [FolderController::class, 'update'])->name('shopping.folders.update');
    Route::delete('shopping/folders/{folder}', [FolderController::class, 'destroy'])->name('shopping.folders.destroy');
    Route::post('shopping/folders/reorder', [FolderController::class, 'reorder'])->name('shopping.folders.reorder');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/webauthn.php';
