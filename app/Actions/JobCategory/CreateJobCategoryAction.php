<?php

declare(strict_types=1);

namespace App\Actions\JobCategory;

use App\Models\JobCategory;
use App\Models\User;

final readonly class CreateJobCategoryAction
{
    /**
     * @param  array{title: string, description?: string|null}  $data
     */
    public function execute(User $user, array $data): JobCategory
    {
        return $user->jobCategories()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
        ]);
    }
}
