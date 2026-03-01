<?php

declare(strict_types=1);

namespace App\Actions\Job;

use App\Models\Job;
use App\Models\User;

final readonly class UpdateJobAction
{
    /**
     * @param  array{
     *     title: string,
     *     company_name: string,
     *     job_status_id: int,
     *     job_category_id: int,
     *     description?: string|null,
     *     job_url?: string|null,
     *     salary?: int|null,
     *     location_city?: string|null,
     * }  $data
     */
    public function execute(User $user, Job $job, array $data): void
    {
        $statusBelongsToUser = $user->jobStatuses()
            ->where('id', $data['job_status_id'])
            ->exists();

        abort_if(! $statusBelongsToUser, 403);

        $categoryBelongsToUser = $user->jobCategories()
            ->where('id', $data['job_category_id'])
            ->exists();

        abort_if(! $categoryBelongsToUser, 403);

        $job->update($data);
    }
}
