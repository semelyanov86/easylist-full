<?php

declare(strict_types=1);

namespace App\Actions\JobStatus;

use App\Models\JobStatus;

final readonly class UpdateJobStatusAction
{
    /**
     * @param  array{title: string, description?: string|null}  $data
     */
    public function execute(JobStatus $jobStatus, array $data): void
    {
        $jobStatus->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
        ]);
    }
}
