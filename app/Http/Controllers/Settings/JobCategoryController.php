<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\JobCategory\CreateJobCategoryAction;
use App\Actions\JobCategory\DeleteJobCategoryAction;
use App\Actions\JobCategory\ReorderJobCategoriesAction;
use App\Actions\JobCategory\UpdateJobCategoryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ReorderJobCategoriesRequest;
use App\Http\Requests\Settings\StoreJobCategoryRequest;
use App\Http\Requests\Settings\UpdateJobCategoryRequest;
use App\Models\JobCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class JobCategoryController extends Controller
{
    /**
     * Создать новую категорию.
     */
    public function store(StoreJobCategoryRequest $request, CreateJobCategoryAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        /** @var array{title: string, description?: string|null} $data */
        $data = $request->validated();

        $action->execute($user, $data);

        return back();
    }

    /**
     * Обновить категорию.
     */
    public function update(UpdateJobCategoryRequest $request, JobCategory $jobCategory, UpdateJobCategoryAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($jobCategory->user_id !== $user->id, 403);

        /** @var array{title: string, description?: string|null} $data */
        $data = $request->validated();

        $action->execute($jobCategory, $data);

        return back();
    }

    /**
     * Удалить категорию.
     */
    public function destroy(Request $request, JobCategory $jobCategory, DeleteJobCategoryAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($jobCategory->user_id !== $user->id, 403);

        $action->execute($jobCategory);

        return back();
    }

    /**
     * Изменить порядок категорий.
     */
    public function reorder(ReorderJobCategoriesRequest $request, ReorderJobCategoriesAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        /** @var array<int, int> $ids */
        $ids = $request->validated('ids');

        $action->execute($user, $ids);

        return back();
    }
}
