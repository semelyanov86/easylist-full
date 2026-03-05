<?php

declare(strict_types=1);

namespace App\Actions\JobTask;

use App\Models\JobTask;
use App\Models\User;

final readonly class ToggleJobTaskAction
{
    public function execute(User $user, JobTask $task): void
    {
        $task->update([
            'completed_at' => $task->completed_at === null ? now() : null,
        ]);

        $job = $task->job;

        if ($job !== null) {
            $event = $task->completed_at !== null ? 'task_completed' : 'task_reopened';
            $message = $task->completed_at !== null ? 'Задача выполнена' : 'Задача возобновлена';

            activity('job')
                ->performedOn($job)
                ->causedBy($user)
                ->withProperties([
                    'task_id' => $task->id,
                    'task_title' => $task->title,
                ])
                ->event($event)
                ->log($message);
        }

        if ($task->external_id !== null && $user->ticktick_token !== null && $user->ticktick_list_id !== null) {
            dispatch(new \App\Jobs\TickTick\SyncTickTickTaskToggled($task->id));
        }
    }
}
