<?php

declare(strict_types=1);

namespace App\Actions\Job;

use App\Data\StatusTabData;
use App\Models\Job;
use App\Models\JobStatus;
use App\Models\User;
use Illuminate\Support\Collection;

final readonly class GetJobStatusTabsAction
{
    /**
     * @return Collection<int, StatusTabData>
     */
    public function execute(User $user): Collection
    {
        /** @var Collection<int, int> $counts */
        $counts = Job::query()
            ->where('user_id', $user->id)
            ->selectRaw('job_status_id, count(*) as jobs_count')
            ->groupBy('job_status_id')
            ->pluck('jobs_count', 'job_status_id');

        /** @var Collection<int, StatusTabData> */
        return $user->jobStatuses()->ordered()->get()->map(
            function (JobStatus $status) use ($counts): StatusTabData {
                /** @var \App\Enums\StatusColor $color */
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
