<?php

declare(strict_types=1);

namespace App\Enums;

enum JobsViewMode: string
{
    case Table = 'table';
    case Kanban = 'kanban';

    /**
     * Список всех строковых значений.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Русская подпись для UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Table => 'Таблица',
            self::Kanban => 'Канбан',
        };
    }
}
