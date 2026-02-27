<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\JobCategory\CreateDefaultJobCategoryAction;
use App\Models\User;
use Illuminate\Database\Seeder;

final class JobCategorySeeder extends Seeder
{
    public function run(): void
    {
        $action = new CreateDefaultJobCategoryAction();

        User::all()->each(fn (User $user) => $action->execute($user));
    }
}
