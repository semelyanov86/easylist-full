<?php

declare(strict_types=1);

namespace App\Actions\JobTask;

use App\Models\JobTask;

final readonly class DeleteJobTaskAction
{
    public function execute(JobTask $task): void
    {
        $job = $task->job;
        $user = $task->user;
        $taskTitle = $task->title;

        $task->delete();

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
