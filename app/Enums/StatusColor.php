<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusColor: string
{
    case Gray = 'gray';
    case Blue = 'blue';
    case Green = 'green';
    case Red = 'red';
    case Amber = 'amber';
    case Purple = 'purple';
    case Pink = 'pink';
    case Cyan = 'cyan';
    case Indigo = 'indigo';
    case Teal = 'teal';
    case Orange = 'orange';
    case Lime = 'lime';
    case Rose = 'rose';
    case Sky = 'sky';
    case Violet = 'violet';

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
            self::Gray => 'Серый',
            self::Blue => 'Синий',
            self::Green => 'Зелёный',
            self::Red => 'Красный',
            self::Amber => 'Янтарный',
            self::Purple => 'Фиолетовый',
            self::Pink => 'Розовый',
            self::Cyan => 'Бирюзовый',
            self::Indigo => 'Индиго',
            self::Teal => 'Бирюзово-зелёный',
            self::Orange => 'Оранжевый',
            self::Lime => 'Лаймовый',
            self::Rose => 'Алый',
            self::Sky => 'Небесный',
            self::Violet => 'Фиалковый',
        };
    }
}
