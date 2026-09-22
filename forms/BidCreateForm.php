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
     *
     * @return array
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
     *
     * @return array
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
     *
     * @return string
     */
    public function formName(): string
    {
        return 'bid';
    }
}
