<?php

declare(strict_types=1);

namespace App\Actions\JobStatus;

use App\Models\JobStatus;

final readonly class DeleteJobStatusAction
{
    public function execute(JobStatus $jobStatus): void
    {
        $jobStatus->delete();
    }
}
