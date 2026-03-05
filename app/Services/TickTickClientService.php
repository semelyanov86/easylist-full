<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\TickTickClientContract;
use App\Exceptions\TickTickException;
use Illuminate\Support\Facades\Http;

/**
 * HTTP-клиент для TickTick Open API.
 */
final readonly class TickTickClientService implements TickTickClientContract
{
    public function __construct(
        private string $baseUrl,
        private int $timeout,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function createTask(string $token, array $data): array
    {
        $response = Http::timeout($this->timeout)
            ->withToken($token)
            ->post("{$this->baseUrl}/task", $data);

        if ($response->failed()) {
            throw TickTickException::requestFailed("HTTP {$response->status()}");
        }

        /** @var string $id */
        $id = $response->json('id', '');

        return ['id' => $id];
    }

    /**
     * {@inheritDoc}
     */
    public function updateTask(string $token, string $taskId, array $data): void
    {
        $response = Http::timeout($this->timeout)
            ->withToken($token)
            ->post("{$this->baseUrl}/task/{$taskId}", $data);

        if ($response->failed()) {
            throw TickTickException::requestFailed("HTTP {$response->status()}");
        }
    }

    /**
     * {@inheritDoc}
     */
    public function completeTask(string $token, string $projectId, string $taskId): void
    {
        $response = Http::timeout($this->timeout)
            ->withToken($token)
            ->post("{$this->baseUrl}/project/{$projectId}/task/{$taskId}/complete");

        if ($response->failed()) {
            throw TickTickException::requestFailed("HTTP {$response->status()}");
        }
    }

    /**
     * {@inheritDoc}
     */
    public function deleteTask(string $token, string $projectId, string $taskId): void
    {
        $response = Http::timeout($this->timeout)
            ->withToken($token)
            ->delete("{$this->baseUrl}/project/{$projectId}/task/{$taskId}");

        if ($response->failed()) {
            throw TickTickException::requestFailed("HTTP {$response->status()}");
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getTask(string $token, string $projectId, string $taskId): array
    {
        $response = Http::timeout($this->timeout)
            ->withToken($token)
            ->get("{$this->baseUrl}/project/{$projectId}/task/{$taskId}");

        if ($response->status() === 404) {
            throw TickTickException::taskNotFound($taskId);
        }

        if ($response->failed()) {
            throw TickTickException::requestFailed("HTTP {$response->status()}");
        }

        /** @var array{status: int} */
        return $response->json();
    }
}
