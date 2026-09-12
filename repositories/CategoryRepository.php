<?php

declare(strict_types=1);

namespace app\repositories;

use app\models\Category;

final class CategoryRepository
{
    /**
     * Returns all categories sorted by name.
     *
     * @return Category[]
     */
    public function findAll(): array
    {
        return Category::find()->orderBy(['name' => SORT_ASC])->all();
    }

    /**
     * Returns categories as an ID-to-name map sorted by name.
     *
     * @return array<int, string>
     */
    public function findAllForSelect(): array
    {
        return Category::find()
            ->select(['name', 'id'])
            ->orderBy(['name' => SORT_ASC])
            ->indexBy('id')
            ->column();
    }
}
