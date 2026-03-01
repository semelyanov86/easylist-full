<?php

declare(strict_types=1);

namespace App\Actions\Job;

use App\Models\Job;

final readonly class ToggleFavoriteAction
{
    public function execute(Job $job): void
    {
        $job->update(['is_favorite' => ! $job->is_favorite]);
    }
}
