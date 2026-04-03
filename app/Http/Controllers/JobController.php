<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Job\CreateJobAction;
use App\Actions\Job\DeleteJobAction;
use App\Actions\Job\GetJobShowDataAction;
use App\Actions\Job\GetJobStatusTabsAction;
use App\Actions\Job\GetKanbanColumnsAction;
use App\Actions\Job\GetUserJobsQuery;
use App\Actions\Job\MoveJobToStatusAction;
use App\Actions\Job\ShareJobAction;
use App\Actions\Job\ToggleFavoriteAction;
use App\Actions\Job\UpdateJobAction;
use App\Actions\JobCategory\GetUserJobCategoriesAction;
use App\Actions\Skill\SyncJobSkillsAction;
use App\Data\JobIndexFiltersData;
use App\Data\SkillData;
use App\Http\Requests\MoveJobRequest;
use App\Http\Requests\StoreJobRequest;
use App\Http\Requests\UpdateJobRequest;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Enums\JobsViewMode;
use App\Models\User;

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
        /** @var User $user */
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

        /** @var JobsViewMode $viewMode */
        $viewMode = $user->jobs_view_mode;

        return Inertia::render('jobs/Index', [
            'jobs' => $getJobs->execute($user, $filters),
            'filters' => $filters,
            'statusTabs' => $getStatusTabs->execute($user),
            'categories' => $getCategories->execute($user),
            'skills' => SkillData::collect($user->skills()->orderBy('title')->get()),
            'viewMode' => $viewMode->value,
            'kanbanColumns' => fn () => $getKanbanColumns->execute($user, $filters),
        ]);
    }

    /**
     * Показать детальную страницу вакансии.
     */
    public function show(
        Request $request,
        Job $job,
        GetJobShowDataAction $getJobData,
        GetJobStatusTabsAction $getStatusTabs,
        GetUserJobCategoriesAction $getCategories,
    ): Response {
        /** @var User $user */
        $user = $request->user();

        abort_if($job->user_id !== $user->id, 403);

        return Inertia::render('jobs/Show', [
            'job' => $getJobData->execute($job),
            'statusTabs' => $getStatusTabs->execute($user),
            'categories' => $getCategories->execute($user),
            'skills' => SkillData::collect($user->skills()->orderBy('title')->get()),
        ]);
    }

    /**
     * Создать новую вакансию.
     */
    public function store(StoreJobRequest $request, CreateJobAction $action, SyncJobSkillsAction $syncSkills): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        /** @var array{title: string, company_name: string, job_status_id: int, job_category_id: int, description?: string|null, job_url?: string|null, resume_version_url?: string|null, salary?: int|null, location_city?: string|null, skill_ids?: list<int>|null} $data */
        $data = $request->validated();

        /** @var list<int> $skillIds */
        $skillIds = $data['skill_ids'] ?? [];
        unset($data['skill_ids']);

        $job = $action->execute($user, $data);

        $syncSkills->execute($user, $job, $skillIds);

        return back();
    }

    /**
     * Обновить вакансию.
     */
    public function update(UpdateJobRequest $request, Job $job, UpdateJobAction $action, SyncJobSkillsAction $syncSkills): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_if($job->user_id !== $user->id, 403);

        /** @var array{title: string, company_name: string, job_status_id: int, job_category_id: int, description?: string|null, job_url?: string|null, resume_version_url?: string|null, salary?: int|null, location_city?: string|null, skill_ids?: list<int>|null} $data */
        $data = $request->validated();

        /** @var list<int> $skillIds */
        $skillIds = $data['skill_ids'] ?? [];
        unset($data['skill_ids']);

        $action->execute($user, $job, $data);

        $syncSkills->execute($user, $job, $skillIds);

        return back();
    }

    /**
     * Переместить вакансию в другой статус.
     */
    public function move(MoveJobRequest $request, Job $job, MoveJobToStatusAction $action): RedirectResponse
    {
        /** @var User $user */
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
        /** @var User $user */
        $user = $request->user();

        abort_if($job->user_id !== $user->id, 403);

        $action->execute($job);

        return back();
    }

    /**
     * Сгенерировать публичную ссылку для вакансии.
     */
    public function share(Request $request, Job $job, ShareJobAction $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_if($job->user_id !== $user->id, 403);

        $uuid = $action->execute($job);

        return response()->json(['uuid' => $uuid]);
    }

    /**
     * Удалить вакансию (soft delete).
     */
    public function destroy(Request $request, Job $job, DeleteJobAction $action): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_if($job->user_id !== $user->id, 403);

        $action->execute($job);

        return back();
    }
}
