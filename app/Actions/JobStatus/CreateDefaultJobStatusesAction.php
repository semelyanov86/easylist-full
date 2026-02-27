<?php

declare(strict_types=1);

namespace App\Actions\JobStatus;

use App\Enums\StatusColor;
use App\Models\User;

final readonly class CreateDefaultJobStatusesAction
{
    /** @var array<string, StatusColor> */
    private const array DEFAULT_STATUSES = [
        'Отложено' => StatusColor::Gray,
        'Подана заявка' => StatusColor::Blue,
        'Первичное собеседование' => StatusColor::Purple,
        'Техническое интервью' => StatusColor::Cyan,
        'Финальный процесс' => StatusColor::Amber,
        'Оффер' => StatusColor::Green,
        'Отклонено' => StatusColor::Red,
        'Отклонено после собеседования' => StatusColor::Pink,
    ];

    /**
     * Идемпотентно создаёт дефолтные статусы для пользователя.
     */
    public function execute(User $user): void
    {
        $order = 1;

        foreach (self::DEFAULT_STATUSES as $title => $color) {
            $user->jobStatuses()->firstOrCreate(
                ['title' => $title],
                ['order_column' => $order, 'color' => $color],
            );

            $order++;
        }
    }
}
