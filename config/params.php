<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'pagination' => [
        'tasksPageSize' => 5,
    ],
    'fileStorage' => [
        // Unlike the assignment requirements, task attachments are stored
        // outside the public web directory and served through a controller.
        'roots' => [
            Sanweb\Taskforce\enum\StorageArea::TaskAttachments->value => '@app/storage/task-attachments',
            Sanweb\Taskforce\enum\StorageArea::UserAvatars->value => '@webroot/uploads/user-avatars',
        ],
    ],
];
