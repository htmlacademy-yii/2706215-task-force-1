<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\enum;

use Sanweb\Taskforce\enum\trait\EnumValues;

/**
 * Bid moderation statuses stored in the database.
 */
enum BidStatus: string
{
    use EnumValues;

    case New = 'new';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

    /**
     * Returns the localized bid status label.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::New => 'Новый',
            self::Accepted => 'Принят',
            self::Rejected => 'Отклонен',
        };
    }
}
