<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\enum\trait;

/**
 * Exposes scalar values of a backed enum.
 */
trait EnumValues
{
    /**
     * Returns scalar values of all enum cases.
     *
     * @return list<int|string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
