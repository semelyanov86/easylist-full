<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Data\DashboardPendingTaskData;
use App\Models\Job;
use App\Models\JobTask;
use App\Models\User;

final readonly class GetDashboardPendingTasksAction
{
    /**
     * @return list<DashboardPendingTaskData>
     */
    public function execute(User $user, int $limit = 10): array
    {
        $tasks = JobTask::query()
            ->where('user_id', $user->id)
            ->whereNull('completed_at')
            ->with('job:id,title,company_name')
            ->orderByRaw('deadline IS NULL, deadline ASC')
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        return array_values($tasks->map(
            function (JobTask $task): DashboardPendingTaskData {
                /** @var Job $job */
                $job = $task->job;

                return new DashboardPendingTaskData(
                    id: $task->id,
                    title: $task->title,
                    deadline: $task->deadline?->toISOString(),
                    job_id: $job->id,
                    job_title: $job->title ?? '',
                    job_company_name: $job->company_name ?? '',
                );
            }
        )->all());
    }
}
