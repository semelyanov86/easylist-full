<?php

declare(strict_types=1);

namespace App\Actions\Job;

use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Str;

final readonly class CreateJobAction
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
    public function execute(User $user, array $data): Job
    {
        $statusBelongsToUser = $user->jobStatuses()
            ->where('id', $data['job_status_id'])
            ->exists();

        abort_if(! $statusBelongsToUser, 403);

        $categoryBelongsToUser = $user->jobCategories()
            ->where('id', $data['job_category_id'])
            ->exists();

        abort_if(! $categoryBelongsToUser, 403);

        /** @var Job */
        return $user->jobs()->create([
            'uuid' => Str::uuid()->toString(),
            ...$data,
        ]);
    }
}
