<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\enum;

enum StorageArea: string
{
    case TaskAttachments = 'taskAttachments';
    case UserAvatars = 'userAvatars';
}
