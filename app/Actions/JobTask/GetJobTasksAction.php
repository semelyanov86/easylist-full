<?php

declare(strict_types=1);

namespace App\Actions\JobTask;

use App\Data\JobTaskData;
use App\Models\Job;
use App\Models\JobTask;

final readonly class GetJobTasksAction
{
    /**
     * @return list<JobTaskData>
     */
    public function execute(Job $job): array
    {
        $tasks = $job->tasks()
            ->latest()
            ->get();

        return array_values($tasks->map(fn (JobTask $task): JobTaskData => new JobTaskData(
            id: $task->id,
            user_id: $task->user_id,
            title: $task->title,
            external_id: $task->external_id,
            deadline: $task->deadline?->toISOString(),
            completed_at: $task->completed_at?->toISOString(),
            created_at: $task->created_at?->toISOString() ?? '',
        ))->all());
    }
}
