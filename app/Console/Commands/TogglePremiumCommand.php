<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

final class TogglePremiumCommand extends Command
{
    protected $signature = 'user:toggle-premium
        {email : Email пользователя}';

    protected $description = 'Переключить premium-статус пользователя';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if ($user === null) {
            $this->error('Пользователь не найден.');

            return self::FAILURE;
        }

        $user->update(['is_premium' => ! $user->is_premium]);

        $status = $user->is_premium ? 'включён' : 'выключен';
        $this->info("Premium-статус {$status} для {$user->email}.");

        return self::SUCCESS;
    }
}
