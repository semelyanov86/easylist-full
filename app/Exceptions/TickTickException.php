<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class TickTickException extends RuntimeException
{
    public static function requestFailed(string $reason): self
    {
        return new self("Ошибка TickTick API: {$reason}");
    }

    public static function taskNotFound(string $taskId): self
    {
        return new self("Задача TickTick не найдена: {$taskId}");
    }
}
