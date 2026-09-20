<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\repositories;

use app\models\City;

/**
 * Provides city read queries.
 */
final class CityRepository
{
    /**
     * Returns cities as an ID-to-name map sorted by name.
     *
     * @return array<int, string>
     */
    public function findAllForSelect(): array
    {
        return City::find()
            ->select(['name', 'id'])
            ->orderBy(['name' => SORT_ASC])
            ->indexBy('id')
            ->column();
    }
}
