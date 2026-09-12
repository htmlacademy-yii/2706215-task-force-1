<?php

declare(strict_types=1);

namespace app\requests;

use app\dto\TaskCreateDto;
use app\models\Category;
use app\models\City;
use app\validators\FileNameValidator;
use DateTimeImmutable;
use yii\base\Model;

/**
 * Validates input submitted through the task creation form.
 */
class TaskCreateRequest extends Model
{
    public string|int $categoryId = '';
    public string $title = '';
    public string $description = '';
    public string|int $budget = '';
    public string $expireDate = '';
    public string|null $location = null;
    public string|int|null $cityId = null;
    public array $files = [];

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['title', 'description', 'location'], 'trim'],
            [['location', 'cityId'], 'default', 'value' => null],

            [['categoryId', 'title', 'description', 'budget', 'expireDate'], 'required'],

            ['title', 'string', 'min' => 5, 'max' => 255],
            ['description', 'string'],
            ['location', 'string', 'max' => 255],

            ['files', 'file', 'skipOnEmpty' => true, 'maxFiles' => 0],
            ['files', FileNameValidator::class],

            ['budget', 'integer', 'min' => 1],

            [
                'expireDate',
                'date',
                'format' => 'php:Y-m-d',
                'min' => (new DateTimeImmutable('tomorrow'))->format('Y-m-d'),
                'tooSmall' => 'Срок исполнения должен быть не раньше завтрашнего дня.',
            ],

            ['categoryId', 'integer'],
            [
                'categoryId',
                'exist',
                'targetClass' => Category::class,
                'targetAttribute' => ['categoryId' => 'id'],
            ],

            ['cityId', 'integer'],
            [
                'cityId',
                'exist',
                'targetClass' => City::class,
                'targetAttribute' => ['cityId' => 'id'],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'categoryId' => 'Категория',
            'title' => 'Опишите суть работы',
            'description' => 'Подробности задания',
            'budget' => 'Бюджет',
            'expireDate' => 'Срок исполнения',
            'location' => 'Локация',
            'files' => 'Файлы',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function formName(): string
    {
        return 'add-task';
    }

    /**
     * Converts validated request data to a DTO.
     */
    public function toDto(): TaskCreateDto
    {
        return new TaskCreateDto(
            categoryId: (int) $this->categoryId,
            title: $this->title,
            description: $this->description,
            budget: (int) $this->budget,
            expireDate: $this->expireDate,
            location: $this->location,
            cityId: $this->cityId === null ? null : (int) $this->cityId,
        );
    }
}
