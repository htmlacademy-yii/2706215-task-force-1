<?php

declare(strict_types=1);

namespace app\forms;

use yii\base\Model;

/**
 * Validates a bid submitted from the task details page.
 */
final class BidCreateForm extends Model
{
    public string|int $price = '';
    public string|null $comment = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['comment'], 'trim'],
            [['comment'], 'default', 'value' => null],
            [['price'], 'required'],
            [['price'], 'integer', 'min' => 1],
            [['comment'], 'string', 'max' => 1000],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'price' => 'Стоимость',
            'comment' => 'Ваш комментарий',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function formName(): string
    {
        return 'bid';
    }
}
