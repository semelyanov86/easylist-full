<?php

declare(strict_types=1);

namespace App\Actions\Job;

use App\Models\Job;
use App\Models\User;

final readonly class MoveJobToStatusAction
{
    public function execute(User $user, Job $job, int $statusId): void
    {
        $statusBelongsToUser = $user->jobStatuses()->where('id', $statusId)->exists();

        abort_if(! $statusBelongsToUser, 403);

        $job->update(['job_status_id' => $statusId]);
    }
}
