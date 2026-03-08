<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\CompanyAnalysisController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\ContactFinderController;
use App\Http\Controllers\Api\V1\FolderController;
use App\Http\Controllers\Api\V1\JobCategoryController;
use App\Http\Controllers\Api\V1\JobCommentController;
use App\Http\Controllers\Api\V1\JobController;
use App\Http\Controllers\Api\V1\JobDocumentController;
use App\Http\Controllers\Api\V1\JobPublicController;
use App\Http\Controllers\Api\V1\ShoppingItemController;
use App\Http\Controllers\Api\V1\ShoppingListController;
use App\Http\Controllers\Api\V1\StatisticsController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    // Публичные эндпоинты (без авторизации)
    Route::get('public/jobs/{uuid}', [JobPublicController::class, 'show'])
        ->name('api.v1.public.jobs.show');
    Route::get('links/{uuid}', [ShoppingListController::class, 'publicShow'])
        ->name('api.v1.public.lists.show');

    Route::middleware('auth:sanctum')->group(function (): void {
        // Авторизованный пользователь
        Route::get('me', [UserController::class, 'show'])
            ->name('api.v1.me');

        // Вакансии
        Route::apiResource('jobs', JobController::class)
            ->only(['index', 'show', 'store', 'update', 'destroy'])
            ->names('api.v1.jobs');

        Route::patch('jobs/{job}/status', [JobController::class, 'moveStatus'])
            ->name('api.v1.jobs.move-status');
        Route::patch('jobs/{job}/favorite', [JobController::class, 'toggleFavorite'])
            ->name('api.v1.jobs.toggle-favorite');
        Route::post('jobs/{job}/share', [JobController::class, 'share'])
            ->name('api.v1.jobs.share');

        // Комментарии вакансии
        Route::get('jobs/{job}/comments', [JobCommentController::class, 'index'])
            ->name('api.v1.jobs.comments.index');
        Route::post('jobs/{job}/comments', [JobCommentController::class, 'store'])
            ->name('api.v1.jobs.comments.store');

        // Документы вакансии
        Route::get('jobs/{job}/documents', [JobDocumentController::class, 'index'])
            ->name('api.v1.jobs.documents.index');
        Route::post('jobs/{job}/documents', [JobDocumentController::class, 'store'])
            ->name('api.v1.jobs.documents.store');

        // Контакты вакансии
        Route::get('jobs/{job}/contacts', [ContactController::class, 'index'])
            ->name('api.v1.jobs.contacts.index');
        Route::post('jobs/{job}/contacts', [ContactController::class, 'store'])
            ->name('api.v1.jobs.contacts.store');
        Route::delete('jobs/{job}/contacts/{contact}', [ContactController::class, 'destroy'])
            ->name('api.v1.jobs.contacts.destroy');

        // Статистика дашборда
        Route::get('statistics', StatisticsController::class)
            ->name('api.v1.statistics');

        // Незавершённые задачи
        Route::get('tasks/pending', [TaskController::class, 'index'])
            ->name('api.v1.tasks.pending');

        // Списки (категории)
        Route::get('job-categories', [JobCategoryController::class, 'index'])
            ->name('api.v1.job-categories.index');
        Route::get('job-categories/{jobCategory}', [JobCategoryController::class, 'show'])
            ->name('api.v1.job-categories.show');
        Route::get('job-categories/{jobCategory}/jobs', [JobCategoryController::class, 'jobs'])
            ->name('api.v1.job-categories.jobs');

        // ИИ-процессы (фоновые)
        Route::post('jobs/{job}/analyze-company', CompanyAnalysisController::class)
            ->name('api.v1.jobs.analyze-company');
        Route::post('jobs/{job}/find-contacts', ContactFinderController::class)
            ->name('api.v1.jobs.find-contacts');

        // Папки
        Route::apiResource('folders', FolderController::class)
            ->only(['index', 'show', 'store', 'update', 'destroy'])
            ->names('api.v1.folders');

        // Списки покупок
        Route::apiResource('lists', ShoppingListController::class)
            ->only(['index', 'show', 'store', 'update', 'destroy'])
            ->parameters(['lists' => 'shoppingList'])
            ->names('api.v1.lists');
        Route::get('folders/{folder}/lists', [ShoppingListController::class, 'fromFolder'])
            ->name('api.v1.folders.lists.index');
        Route::post('lists/{shoppingList}/email', [ShoppingListController::class, 'sendEmail'])
            ->name('api.v1.lists.email');

        // Позиции списка покупок
        Route::apiResource('items', ShoppingItemController::class)
            ->only(['index', 'show', 'store', 'update', 'destroy'])
            ->parameters(['items' => 'shoppingItem'])
            ->names('api.v1.items');
        Route::get('lists/{shoppingList}/items', [ShoppingItemController::class, 'fromList'])
            ->name('api.v1.lists.items.index');
        Route::patch('lists/{shoppingList}/items/undone', [ShoppingItemController::class, 'uncrossAll'])
            ->name('api.v1.lists.items.uncross');
        Route::delete('lists/{shoppingList}/items', [ShoppingItemController::class, 'destroyAll'])
            ->name('api.v1.lists.items.destroy-all');
    });
});
