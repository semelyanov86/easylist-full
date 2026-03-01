<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\Skill\CreateDefaultSkillsAction;
use App\Models\User;
use Illuminate\Database\Seeder;

final class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $action = new CreateDefaultSkillsAction();

        User::all()->each(fn (User $user) => $action->execute($user));
    }
}
