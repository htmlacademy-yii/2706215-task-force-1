<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\enum;

enum MyTaskFilter: string
{
    case New = 'new';
    case InProgress = 'in_progress';
    case Overdue = 'overdue';
    case Closed = 'closed';

    /**
     * Returns filters available to the given role.
     *
     * @return list<self>
     */
    public static function availableFor(bool $isExecutor): array
    {
        if ($isExecutor) {
            return [self::InProgress, self::Overdue, self::Closed];
        }

        return [self::New, self::InProgress, self::Closed];
    }

    public static function defaultFor(bool $isExecutor): self
    {
        return self::availableFor($isExecutor)[0];
    }

    public static function fromRequest(?string $value, bool $isExecutor): self
    {
        $filter = $value === null ? null : self::tryFrom($value);

        if ($filter === null || !in_array($filter, self::availableFor($isExecutor), true)) {
            return self::defaultFor($isExecutor);
        }

        return $filter;
    }

    public function label(): string
    {
        return match ($this) {
            self::New => 'Новые',
            self::InProgress => 'В процессе',
            self::Overdue => 'Просрочено',
            self::Closed => 'Закрытые',
        };
    }

    public function heading(): string
    {
        return match ($this) {
            self::New => 'Новые задания',
            self::InProgress => 'Задания в процессе',
            self::Overdue => 'Просроченные задания',
            self::Closed => 'Закрытые задания',
        };
    }
}
