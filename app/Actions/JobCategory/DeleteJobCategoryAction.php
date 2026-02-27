<?php

declare(strict_types=1);

namespace App\Actions\JobCategory;

use App\Models\JobCategory;

final readonly class DeleteJobCategoryAction
{
    public function execute(JobCategory $jobCategory): void
    {
        $jobCategory->delete();
    }
}
