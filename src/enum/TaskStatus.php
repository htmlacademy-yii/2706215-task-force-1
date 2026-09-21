<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\enum;

use Sanweb\Taskforce\enum\trait\EnumValues;
use Sanweb\Taskforce\exception\MissingEnumLabelException;

/**
 * Task lifecycle statuses stored in the database.
 */
enum TaskStatus: string
{
    use EnumValues;

    case New = 'new';
    case Canceled = 'canceled';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Failed = 'failed';

    /**
     * Returns the localized status label.
     */
    public function label(): string
    {
        return match ($this) {
            self::New => 'Новое',
            self::Canceled => 'Отменено',
            self::InProgress => 'В работе',
            self::Completed => 'Выполнено',
            self::Failed => 'Провалено',
            default => throw new MissingEnumLabelException(
                "Отображаемое название для статуса {$this->value} не задано"
            )
        };
    }
}
