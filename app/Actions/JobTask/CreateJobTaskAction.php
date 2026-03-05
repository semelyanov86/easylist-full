<?php

declare(strict_types=1);

namespace App\Actions\JobTask;

use App\Models\Job;
use App\Models\JobTask;
use App\Models\User;

final readonly class CreateJobTaskAction
{
    /**
     * @param  array{title: string, deadline?: ?string}  $data
     */
    public function execute(User $user, Job $job, array $data): JobTask
    {
        /** @var JobTask $task */
        $task = $job->tasks()->create([
            'user_id' => $user->id,
            ...$data,
        ]);

        activity('job')
            ->performedOn($job)
            ->causedBy($user)
            ->withProperties([
                'task_id' => $task->id,
                'task_title' => $task->title,
            ])
            ->event('task_added')
            ->log('Добавлена задача');

        if ($user->ticktick_token !== null && $user->ticktick_list_id !== null) {
            dispatch(new \App\Jobs\TickTick\SyncTickTickTaskCreated($task->id));
        }

        return $task;
    }
}
