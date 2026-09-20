<?php

return [
    'pagination' => [
        'tasksPageSize' => 5,
    ],
    'yandex' => [
        'geocoderApiKey' => $_ENV['YANDEX_GEOCODER_API_KEY'] ?? '',
        'mapsApiKey' => $_ENV['YANDEX_MAPS_API_KEY'] ?? '',
    ],
    'github' => [
        'clientId' => $_ENV['GITHUB_CLIENT_ID'] ?? '',
        'clientSecret' => $_ENV['GITHUB_CLIENT_SECRET'] ?? '',
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
