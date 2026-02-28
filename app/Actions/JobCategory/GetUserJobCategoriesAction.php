<?php

declare(strict_types=1);

namespace App\Actions\JobCategory;

use App\Data\JobCategoryData;
use App\Models\User;
use Illuminate\Support\Collection;

final readonly class GetUserJobCategoriesAction
{
    /**
     * @return Collection<int, JobCategoryData>
     */
    public function execute(User $user): Collection
    {
        return JobCategoryData::collect(
            $user->jobCategories()->ordered()->get(),
        );
    }
}
