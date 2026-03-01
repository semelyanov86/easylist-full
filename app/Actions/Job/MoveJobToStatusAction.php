<?php

declare(strict_types=1);

namespace App\Actions\Job;

use App\Models\Job;
use App\Models\JobStatus;
use App\Models\User;

final readonly class MoveJobToStatusAction
{
    public function execute(User $user, Job $job, int $statusId): void
    {
        $statusBelongsToUser = $user->jobStatuses()->where('id', $statusId)->exists();

        abort_if(! $statusBelongsToUser, 403);

        $job->loadMissing('status');

        $oldStatusName = $job->status->title; // @phpstan-ignore property.nonObject
        $newStatus = JobStatus::findOrFail($statusId);

        $job->disableLogging();
        $job->update(['job_status_id' => $statusId]);
        $job->enableLogging();

        activity('job')
            ->performedOn($job)
            ->causedBy($user)
            ->withProperties([
                'old_status' => $oldStatusName,
                'new_status' => $newStatus->title,
            ])
            ->event('status_changed')
            ->log('Статус изменён');
    }
}
