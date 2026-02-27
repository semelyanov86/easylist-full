<?php

declare(strict_types=1);

namespace App\Actions\JobCategory;

use App\Models\User;

final readonly class CreateDefaultJobCategoryAction
{
    /**
     * Идемпотентно создаёт дефолтную категорию для пользователя.
     */
    public function execute(User $user): void
    {
        $user->jobCategories()->firstOrCreate(
            ['title' => 'Общее'],
            ['order_column' => 1],
        );
    }
}
