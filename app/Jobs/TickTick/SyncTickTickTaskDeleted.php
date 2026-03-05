<?php

declare(strict_types=1);

namespace App\Jobs\TickTick;

use App\Contracts\TickTickClientContract;
use App\Exceptions\TickTickException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class SyncTickTickTaskDeleted implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        private readonly string $externalId,
        private readonly string $token,
        private readonly string $projectId,
    ) {}

    public function handle(TickTickClientContract $client): void
    {
        try {
            $client->deleteTask($this->token, $this->projectId, $this->externalId);
        } catch (TickTickException $e) {
            if (str_contains($e->getMessage(), 'не найдена')) {
                return;
            }

            throw $e;
        }
    }
}
