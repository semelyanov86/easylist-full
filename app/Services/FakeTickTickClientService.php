<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\TickTickClientContract;
use App\Exceptions\TickTickException;

final class FakeTickTickClientService implements TickTickClientContract
{
    /** @var list<array{method: string, args: array<int, mixed>}> */
    private array $calls = [];

    private string $createdId = 'fake-ticktick-id';

    private int $taskStatus = 0;

    private bool $shouldFail = false;

    private bool $shouldReturn404 = false;

    /**
     * Установить ID, возвращаемый при создании задачи.
     */
    public function withCreatedId(string $id): self
    {
        $this->createdId = $id;

        return $this;
    }

    /**
     * Установить статус задачи для getTask.
     */
    public function withTaskStatus(int $status): self
    {
        $this->taskStatus = $status;

        return $this;
    }

    /**
     * Заставить фейк выбросить исключение.
     */
    public function shouldFail(): self
    {
        $this->shouldFail = true;

        return $this;
    }

    /**
     * Заставить getTask вернуть 404.
     */
    public function shouldReturn404(): self
    {
        $this->shouldReturn404 = true;

        return $this;
    }

    /**
     * Получить список вызовов.
     *
     * @return list<array{method: string, args: array<int, mixed>}>
     */
    public function getCalls(): array
    {
        return $this->calls;
    }

    /**
     * Проверить, был ли вызван метод.
     */
    public function assertCalled(string $method, int $times = 1): void
    {
        $count = count(array_filter($this->calls, fn (array $call): bool => $call['method'] === $method));

        assert($count === $times, "Ожидалось {$times} вызов(ов) {$method}, получено {$count}");
    }

    /**
     * {@inheritDoc}
     */
    public function createTask(string $token, array $data): array
    {
        $this->calls[] = ['method' => 'createTask', 'args' => [$token, $data]];

        if ($this->shouldFail) {
            throw TickTickException::requestFailed('Сервис недоступен');
        }

        return ['id' => $this->createdId];
    }

    /**
     * {@inheritDoc}
     */
    public function updateTask(string $token, string $taskId, array $data): void
    {
        $this->calls[] = ['method' => 'updateTask', 'args' => [$token, $taskId, $data]];

        if ($this->shouldFail) {
            throw TickTickException::requestFailed('Сервис недоступен');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function completeTask(string $token, string $projectId, string $taskId): void
    {
        $this->calls[] = ['method' => 'completeTask', 'args' => [$token, $projectId, $taskId]];

        if ($this->shouldFail) {
            throw TickTickException::requestFailed('Сервис недоступен');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function deleteTask(string $token, string $projectId, string $taskId): void
    {
        $this->calls[] = ['method' => 'deleteTask', 'args' => [$token, $projectId, $taskId]];

        if ($this->shouldFail) {
            throw TickTickException::requestFailed('Сервис недоступен');
        }

        if ($this->shouldReturn404) {
            throw TickTickException::taskNotFound($taskId);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getTask(string $token, string $projectId, string $taskId): array
    {
        $this->calls[] = ['method' => 'getTask', 'args' => [$token, $projectId, $taskId]];

        if ($this->shouldReturn404) {
            throw TickTickException::taskNotFound($taskId);
        }

        if ($this->shouldFail) {
            throw TickTickException::requestFailed('Сервис недоступен');
        }

        return ['status' => $this->taskStatus];
    }
}
