<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\JobTask\CreateJobTaskAction;
use App\Actions\JobTask\DeleteJobTaskAction;
use App\Actions\JobTask\ToggleJobTaskAction;
use App\Actions\JobTask\UpdateJobTaskAction;
use App\Http\Requests\StoreJobTaskRequest;
use App\Http\Requests\UpdateJobTaskRequest;
use App\Models\Job;
use App\Models\JobTask;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class JobTaskController extends Controller
{
    /**
     * Создать задачу к вакансии.
     */
    public function store(StoreJobTaskRequest $request, Job $job, CreateJobTaskAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($job->user_id !== $user->id, 403);

        /** @var array{title: string, deadline?: ?string} $data */
        $data = $request->validated();

        $action->execute($user, $job, $data);

        return back();
    }

    /**
     * Обновить задачу.
     */
    public function update(UpdateJobTaskRequest $request, JobTask $jobTask, UpdateJobTaskAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($jobTask->user_id !== $user->id, 403);

        /** @var array{title?: string, deadline?: ?string} $data */
        $data = $request->validated();

        $action->execute($jobTask, $data);

        return back();
    }

    /**
     * Переключить выполнение задачи.
     */
    public function toggle(Request $request, JobTask $jobTask, ToggleJobTaskAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($jobTask->user_id !== $user->id, 403);

        $action->execute($user, $jobTask);

        return back();
    }

    /**
     * Удалить задачу.
     */
    public function destroy(Request $request, JobTask $jobTask, DeleteJobTaskAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($jobTask->user_id !== $user->id, 403);

        $action->execute($jobTask);

        return back();
    }
}
