<?php

declare(strict_types=1);

namespace App\Actions\JobStatus;

use App\Data\JobStatusData;
use App\Models\User;
use Illuminate\Support\Collection;

final readonly class GetUserJobStatusesAction
{
    /**
     * @return Collection<int, JobStatusData>
     */
    public function execute(User $user): Collection
    {
        return JobStatusData::collect(
            $user->jobStatuses()->ordered()->get(),
        );
    }
}
