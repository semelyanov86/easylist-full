<?php

declare(strict_types=1);

namespace App\Actions\Job;

use App\Models\Job;

final readonly class DeleteJobAction
{
    public function execute(Job $job): void
    {
        $job->delete();
    }
}
