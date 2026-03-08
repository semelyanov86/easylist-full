<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Data\DashboardJobItemData;
use App\Models\Job;
use App\Models\JobStatus;
use App\Models\User;

final readonly class GetDashboardRecentJobsAction
{
    /**
     * @return list<DashboardJobItemData>
     */
    public function execute(User $user, int $limit = 10): array
    {
        $jobs = Job::query()
            ->where('user_id', $user->id)
            ->with('status:id,title,color')
            ->latest()
            ->limit($limit)
            ->get();

        return array_values($jobs->map(
            function (Job $job): DashboardJobItemData {
                /** @var JobStatus $status */
                $status = $job->status;

                /** @var \App\Enums\StatusColor $color */
                $color = $status->color;

                return new DashboardJobItemData(
                    id: $job->id,
                    title: $job->title ?? '',
                    company_name: $job->company_name ?? '',
                    status_title: $status->title ?? '',
                    status_color: $color,
                    created_at: $job->created_at?->toISOString() ?? '',
                );
            }
        )->all());
    }
}
