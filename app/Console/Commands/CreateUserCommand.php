<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CreateUserCommand extends Command
{
    protected $signature = 'user:create
        {name : Имя пользователя}
        {email : Email пользователя}
        {--premium : Создать пользователя с premium-статусом}';

    protected $description = 'Создать нового пользователя';

    public function handle(CreateNewUser $action): int
    {
        $password = Str::password(16);

        try {
            $action->create([
                'name' => (string) $this->argument('name'),
                'email' => (string) $this->argument('email'),
                'password' => $password,
                'password_confirmation' => $password,
                'is_premium' => (string) $this->option('premium'),
            ]);
        } catch (ValidationException $e) {
            /** @var list<string> $messages */
            foreach ($e->errors() as $messages) {
                foreach ($messages as $message) {
                    $this->error((string) $message);
                }
            }

            return self::FAILURE;
        }

        $this->info('Пользователь успешно создан.');
        $this->line("Пароль: <comment>{$password}</comment>");

        return self::SUCCESS;
    }
}
