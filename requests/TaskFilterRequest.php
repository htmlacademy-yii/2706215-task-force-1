<?php

declare(strict_types=1);

namespace app\requests;

use app\dto\TaskFilterDto;
use yii\base\Model;

/**
 * Validates and normalizes task list filter input.
 */
class TaskFilterRequest extends Model
{
    /**
     * Supported relative publication periods and their labels.
     */
    public const array PERIODS = [
        '-1 hour' => 'За час',
        '-12 hours' => 'За 12 часов',
        '-24 hours' => 'За сутки',
    ];

    public array|string $categories = [];
    public string|int|bool $isRemote = false;
    public string|int|bool $hasNoBid = false;
    public string $period = '';

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [
                ['categories'],
                'filter',
                'filter' => static fn($value): array => $value === '' || $value === null ? [] : (array) $value,
            ],
            [['categories'], 'each', 'rule' => ['integer']],
            [['isRemote', 'hasNoBid'], 'boolean'],
            [['period'], 'in', 'range' => array_keys(self::PERIODS)],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'categories' => 'Категории',
            'isRemote' => 'Удалённая работа',
            'hasNoBid' => 'Без откликов',
            'period' => 'Период',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function formName(): string
    {
        return 'filters';
    }

    /**
     * Converts validated request data to a DTO.
     */
    public function toDto(): TaskFilterDto
    {
        return new TaskFilterDto(
            categories: $this->categories,
            isRemote: (bool) $this->isRemote,
            hasNoBid: (bool) $this->hasNoBid,
            createdAfter: $this->period !== ''
                ? (strtotime($this->period) ?: null)
                : null,
        );
    }
}
