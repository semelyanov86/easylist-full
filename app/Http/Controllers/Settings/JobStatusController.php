<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\JobStatus\CreateJobStatusAction;
use App\Actions\JobStatus\DeleteJobStatusAction;
use App\Actions\JobStatus\GetUserJobStatusesAction;
use App\Actions\JobStatus\ReorderJobStatusesAction;
use App\Actions\JobStatus\UpdateJobStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ReorderJobStatusesRequest;
use App\Http\Requests\Settings\StoreJobStatusRequest;
use App\Http\Requests\Settings\UpdateJobStatusRequest;
use App\Models\JobStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class JobStatusController extends Controller
{
    /**
     * Показать страницу управления статусами откликов.
     */
    public function index(Request $request, GetUserJobStatusesAction $action): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return Inertia::render('settings/JobStatuses', [
            'statuses' => $action->execute($user),
        ]);
    }

    /**
     * Создать новый статус.
     */
    public function store(StoreJobStatusRequest $request, CreateJobStatusAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        /** @var array{title: string, description?: string|null, color: string} $data */
        $data = $request->validated();

        $action->execute($user, $data);

        return to_route('job-statuses.index');
    }

    /**
     * Обновить статус.
     */
    public function update(UpdateJobStatusRequest $request, JobStatus $jobStatus, UpdateJobStatusAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($jobStatus->user_id !== $user->id, 403);

        /** @var array{title: string, description?: string|null, color: string} $data */
        $data = $request->validated();

        $action->execute($jobStatus, $data);

        return back();
    }

    /**
     * Удалить статус.
     */
    public function destroy(Request $request, JobStatus $jobStatus, DeleteJobStatusAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($jobStatus->user_id !== $user->id, 403);

        $action->execute($jobStatus);

        return back();
    }

    /**
     * Изменить порядок статусов.
     */
    public function reorder(ReorderJobStatusesRequest $request, ReorderJobStatusesAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        /** @var array<int, int> $ids */
        $ids = $request->validated('ids');

        $action->execute($user, $ids);

        return back();
    }
}
