<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Data\StatusTabData;
use App\Models\Job;
use App\Models\JobStatus;
use App\Models\User;
use Illuminate\Support\Collection;
use App\Enums\StatusColor;

final readonly class GetDashboardJobFunnelAction
{
    /**
     * @return Collection<int, StatusTabData>
     */
    public function execute(User $user, ?int $categoryId = null): Collection
    {
        /** @var Collection<int, int> $counts */
        $counts = Job::query()
            ->where('user_id', $user->id)
            ->when($categoryId, fn ($query, int $id) => $query->where('job_category_id', $id))
            ->selectRaw('job_status_id, count(*) as jobs_count')
            ->groupBy('job_status_id')
            ->pluck('jobs_count', 'job_status_id');

        /** @var Collection<int, StatusTabData> */
        return $user->jobStatuses()->ordered()->get()->map(
            function (JobStatus $status) use ($counts): StatusTabData {
                /** @var StatusColor $color */
                $color = $status->color;

                return new StatusTabData(
                    id: $status->id,
                    title: $status->title,
                    color: $color,
                    count: $counts->get($status->id, 0),
                );
            },
        );
    }
}
