<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\enum;

use Sanweb\Taskforce\enum\trait\EnumNames;
use Sanweb\Taskforce\enum\trait\EnumValues;
use Sanweb\Taskforce\exception\MissingEnumLabelException;

enum BidStatus: string
{
    use EnumNames, EnumValues;

    case New = 'new';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Новый',
            self::Accepted => 'Принят',
            self::Rejected => 'Отклонен',
            default => throw new MissingEnumLabelException(
                "Отображаемое название для статуса {$this->value} не задано"
            )
        };
    }
}
