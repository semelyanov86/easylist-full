<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Job;
use App\Models\JobDocument;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class DeleteUserCommand extends Command
{
    protected $signature = 'user:delete
        {email : Email пользователя}
        {--force : Удалить без подтверждения}';

    protected $description = 'Удалить пользователя вместе с его вакансиями и документами';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if ($user === null) {
            $this->error('Пользователь не найден.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm("Удалить пользователя {$user->email} и все его данные?")) {
            $this->info('Операция отменена.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($user): void {
            $this->deleteUserDocumentFiles($user);

            $user->jobs()->withTrashed()->each(function ($job): void {
                $job->documents()->delete();
                $job->comments()->delete();
                $job->skills()->detach();
                $job->forceDelete();
            });

            $user->skills()->delete();
            $user->jobStatuses()->delete();
            $user->jobCategories()->delete();
            $user->delete();
        });

        $this->info("Пользователь {$user->email} и все его данные удалены.");

        return self::SUCCESS;
    }

    private function deleteUserDocumentFiles(User $user): void
    {
        $user->jobs()->withTrashed()->each(function (Job $job): void {
            $job->documents()->whereNotNull('file_path')->each(function (JobDocument $document): void {
                if ($document->file_path !== null) {
                    Storage::delete($document->file_path);
                }
            });
        });
    }
}
