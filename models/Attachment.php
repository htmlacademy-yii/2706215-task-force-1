<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "attachment".
 *
 * @property int $id
 * @property int $task_id
 * @property string $file_path
 * @property string $original_name
 * @property string|null $mime_type
 * @property int|null $size_bytes
 * @property string $created_at
 *
 * @property Task $task
 */
class Attachment extends ActiveRecord
{
    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public static function tableName(): string
    {
        return 'attachment';
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            [['mime_type', 'size_bytes'], 'default', 'value' => null],
            [['task_id', 'file_path', 'original_name'], 'required'],
            [['task_id', 'size_bytes'], 'integer'],
            [['file_path', 'original_name', 'mime_type'], 'string', 'max' => 255],
            [['task_id'], 'exist', 'targetClass' => Task::class, 'targetAttribute' => ['task_id' => 'id']],
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
            'id' => 'ID',
            'task_id' => 'Task ID',
            'file_path' => 'File Path',
            'original_name' => 'Original Name',
            'mime_type' => 'Mime Type',
            'size_bytes' => 'Size Bytes',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets the task this attachment belongs to.
     *
     * @return ActiveQuery
     */
    public function getTask(): ActiveQuery
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }
}
