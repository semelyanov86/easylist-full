<?php

declare(strict_types=1);

namespace App\Actions\JobStatus;

use App\Models\User;

final readonly class CreateDefaultJobStatusesAction
{
    /** @var list<string> */
    private const array DEFAULT_TITLES = [
        'Отложено',
        'Подана заявка',
        'Первичное собеседование',
        'Техническое интервью',
        'Финальный процесс',
        'Оффер',
        'Отклонено',
        'Отклонено после собеседования',
    ];

    /**
     * Идемпотентно создаёт дефолтные статусы для пользователя.
     */
    public function execute(User $user): void
    {
        foreach (self::DEFAULT_TITLES as $order => $title) {
            $user->jobStatuses()->firstOrCreate(
                ['title' => $title],
                ['order_column' => $order + 1],
            );
        }
    }
}
