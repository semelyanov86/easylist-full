<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Job\CreateJobAction;
use App\Actions\Job\DeleteJobAction;
use App\Actions\Job\GetJobStatusTabsAction;
use App\Actions\Job\GetKanbanColumnsAction;
use App\Actions\Job\GetUserJobsQuery;
use App\Actions\Job\MoveJobToStatusAction;
use App\Actions\Job\ToggleFavoriteAction;
use App\Actions\JobCategory\GetUserJobCategoriesAction;
use App\Data\JobIndexFiltersData;
use App\Http\Requests\MoveJobRequest;
use App\Http\Requests\StoreJobRequest;
use App\Models\Job;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class JobController extends Controller
{
    /**
     * Показать список вакансий пользователя.
     */
    public function index(
        Request $request,
        GetUserJobsQuery $getJobs,
        GetJobStatusTabsAction $getStatusTabs,
        GetUserJobCategoriesAction $getCategories,
        GetKanbanColumnsAction $getKanbanColumns,
    ): Response {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $filters = JobIndexFiltersData::from([
            'search' => $request->query('search'),
            'status_id' => $request->query('status_id') !== null ? (int) $request->query('status_id') : null,
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'job_category_id' => $request->query('job_category_id') !== null ? (int) $request->query('job_category_id') : null,
            'is_favorite' => $request->query('is_favorite') !== null ? (bool) $request->query('is_favorite') : null,
            'sort' => $request->query('sort'),
        ]);

        /** @var \App\Enums\JobsViewMode $viewMode */
        $viewMode = $user->jobs_view_mode;

        return Inertia::render('jobs/Index', [
            'jobs' => $getJobs->execute($user, $filters),
            'filters' => $filters,
            'statusTabs' => $getStatusTabs->execute($user),
            'categories' => $getCategories->execute($user),
            'viewMode' => $viewMode->value,
            'kanbanColumns' => fn () => $getKanbanColumns->execute($user, $filters),
        ]);
    }

    /**
     * Создать новую вакансию.
     */
    public function store(StoreJobRequest $request, CreateJobAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        /** @var array{title: string, company_name: string, job_status_id: int, job_category_id: int, description?: string|null, job_url?: string|null, salary?: int|null, location_city?: string|null} $data */
        $data = $request->validated();

        $action->execute($user, $data);

        return back();
    }

    /**
     * Переместить вакансию в другой статус.
     */
    public function move(MoveJobRequest $request, Job $job, MoveJobToStatusAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($job->user_id !== $user->id, 403);

        /** @var int $statusId */
        $statusId = $request->validated('status_id');

        $action->execute($user, $job, $statusId);

        return back();
    }

    /**
     * Переключить статус избранного для вакансии.
     */
    public function toggleFavorite(Request $request, Job $job, ToggleFavoriteAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($job->user_id !== $user->id, 403);

        $action->execute($job);

        return back();
    }

    /**
     * Удалить вакансию (soft delete).
     */
    public function destroy(Request $request, Job $job, DeleteJobAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($job->user_id !== $user->id, 403);

        $action->execute($job);

        return back();
    }
}
