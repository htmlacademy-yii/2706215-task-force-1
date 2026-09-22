<?php

declare(strict_types=1);

namespace app\components;

/**
 * Resolves stored avatar values to safe public URLs.
 */
final class AvatarUrlResolver
{
    public const DEFAULT_AVATAR = '/img/avatars/default.png';
    public const LOCAL_PREFIX = '/uploads/user-avatars/';

    /**
     * Returns an external HTTPS URL, a local avatar URL, or the default avatar.
     *
     * @param ?string $avatar
     *
     * @return string
     */
    public function resolve(?string $avatar): string
    {
        if ($avatar === null || $avatar === '') {
            return self::DEFAULT_AVATAR;
        }

        if (filter_var($avatar, FILTER_VALIDATE_URL) !== false) {
            return strtolower((string) parse_url($avatar, PHP_URL_SCHEME)) === 'https'
                ? $avatar
                : self::DEFAULT_AVATAR;
        }

        return $this->isLocalKey($avatar)
            ? self::LOCAL_PREFIX . $avatar
            : self::DEFAULT_AVATAR;
    }

    /**
     * Checks whether the value matches a locally stored avatar key.
     *
     * @param ?string $avatar
     *
     * @return bool
     */
    public function isLocalKey(?string $avatar): bool
    {
        return $avatar !== null
            && preg_match('/^\d+\/[a-f0-9]{32}(?:\.[a-z0-9]+)?$/i', $avatar) === 1;
    }
}
