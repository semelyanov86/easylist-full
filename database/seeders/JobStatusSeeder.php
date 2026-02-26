<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\JobStatus\CreateDefaultJobStatusesAction;
use App\Models\User;
use Illuminate\Database\Seeder;

final class JobStatusSeeder extends Seeder
{
    public function run(): void
    {
        $action = new CreateDefaultJobStatusesAction();

        User::all()->each(fn (User $user) => $action->execute($user));
    }
}
