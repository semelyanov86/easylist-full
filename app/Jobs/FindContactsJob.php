<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\Ai\FindContactsAction;
use App\Models\Job;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Фоновый запуск поиска контактов через ИИ.
 */
final class FindContactsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly int $userId,
        private readonly int $jobId,
    ) {}

    public function handle(FindContactsAction $action): void
    {
        $user = User::findOrFail($this->userId);
        $job = Job::findOrFail($this->jobId);

        $action->execute($user, $job);
    }
}
