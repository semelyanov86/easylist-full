<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Enums\JobsViewMode;
use App\Models\User;

final readonly class UpdateJobsViewModeAction
{
    public function execute(User $user, JobsViewMode $viewMode): void
    {
        $user->update(['jobs_view_mode' => $viewMode]);
    }
}
