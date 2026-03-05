<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Exceptions\TickTickException;

interface TickTickClientContract
{
    /**
     * Создать задачу в TickTick.
     *
     * @param  array<string, mixed>  $data
     * @return array{id: string}
     *
     * @throws TickTickException
     */
    public function createTask(string $token, array $data): array;

    /**
     * Обновить задачу в TickTick.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws TickTickException
     */
    public function updateTask(string $token, string $taskId, array $data): void;

    /**
     * Завершить задачу в TickTick.
     *
     * @throws TickTickException
     */
    public function completeTask(string $token, string $projectId, string $taskId): void;

    /**
     * Удалить задачу в TickTick.
     *
     * @throws TickTickException
     */
    public function deleteTask(string $token, string $projectId, string $taskId): void;

    /**
     * Получить задачу из TickTick.
     *
     * @return array{status: int}
     *
     * @throws TickTickException
     */
    public function getTask(string $token, string $projectId, string $taskId): array;
}
