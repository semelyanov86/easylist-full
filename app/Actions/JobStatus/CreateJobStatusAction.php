<?php

declare(strict_types=1);

namespace App\Actions\JobStatus;

use App\Models\JobStatus;
use App\Models\User;

final readonly class CreateJobStatusAction
{
    /**
     * @param  array{title: string, description?: string|null}  $data
     */
    public function execute(User $user, array $data): JobStatus
    {
        return $user->jobStatuses()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
        ]);
    }
}
