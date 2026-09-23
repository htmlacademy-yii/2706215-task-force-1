<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\enum;

use Sanweb\Taskforce\enum\trait\EnumValues;

/**
 * Identifies an action supported by the task workflow.
 */
enum TaskAction: string
{
    use EnumValues;

    case Cancel = 'cancel';
    case Bid = 'bid';
    case Assign = 'assign';
    case Complete = 'complete';
    case Refuse = 'refuse';

    /**
     * Returns the human-readable action label.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::Cancel => 'Отменить',
            self::Bid => 'Откликнуться',
            self::Assign => 'Назначить',
            self::Complete => 'Завершить',
            self::Refuse => 'Отказаться',
        };
    }
}
