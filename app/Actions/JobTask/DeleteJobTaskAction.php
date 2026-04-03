<?php

declare(strict_types=1);

namespace App\Actions\JobTask;

use App\Models\JobTask;
use App\Jobs\TickTick\SyncTickTickTaskDeleted;

final readonly class DeleteJobTaskAction
{
    public function execute(JobTask $task): void
    {
        $job = $task->job;
        $user = $task->user;
        $taskTitle = $task->title;
        $externalId = $task->external_id;

        $task->delete();

        if ($externalId !== null && $user !== null && $user->ticktick_token !== null && $user->ticktick_list_id !== null) {
            dispatch(new SyncTickTickTaskDeleted($externalId, $user->ticktick_token, $user->ticktick_list_id));
        }

        if ($job !== null && $user !== null) {
            activity('job')
                ->performedOn($job)
                ->causedBy($user)
                ->withProperties([
                    'task_title' => $taskTitle,
                ])
                ->event('task_removed')
                ->log('Удалена задача');
        }
    }
}
