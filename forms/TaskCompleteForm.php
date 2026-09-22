<?php

declare(strict_types=1);

namespace app\forms;

use yii\base\Model;

/**
 * Validates a review submitted when a task is completed.
 */
final class TaskCompleteForm extends Model
{
    public string $comment = '';
    public string|int $score = '';

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            [['comment'], 'trim'],
            [['comment', 'score'], 'required'],
            [['comment'], 'string'],
            [['score'], 'integer', 'min' => 1, 'max' => 5],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'comment' => 'Ваш комментарий',
            'score' => 'Оценка работы',
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function formName(): string
    {
        return 'completion';
    }
}
