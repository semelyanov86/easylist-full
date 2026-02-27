<?php

declare(strict_types=1);

namespace App\Actions\JobCategory;

use App\Models\JobCategory;

final readonly class UpdateJobCategoryAction
{
    /**
     * @param  array{title: string, description?: string|null}  $data
     */
    public function execute(JobCategory $jobCategory, array $data): void
    {
        $jobCategory->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
        ]);
    }
}
