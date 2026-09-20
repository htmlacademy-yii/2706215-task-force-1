<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\enum;

use Sanweb\Taskforce\enum\trait\EnumValues;
use Sanweb\Taskforce\exception\MissingEnumLabelException;

/**
 * User roles available in TaskForce.
 */
enum UserRole: string
{
    use EnumValues;

    case Customer = 'customer';
    case Executor = 'executor';

    /**
     * Returns the localized role label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Customer => 'Заказчик',
            self::Executor => 'Исполнитель',
            default => throw new MissingEnumLabelException(
                "Отображаемое название для роли пользователя {$this->value} не задано"
            )
        };
    }
}
