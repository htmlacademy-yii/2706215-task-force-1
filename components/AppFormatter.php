<?php

declare(strict_types=1);

namespace app\components;

use yii\i18n\Formatter;

/**
 * Adds application-specific value formatting to the Yii formatter.
 */
final class AppFormatter extends Formatter
{
    /**
     * Formats a Russian phone number in the form "+7 (XXX) XXX-XX-XX".
     *
     * Returns the original value if it cannot be formatted.
     *
     * @param ?string $value
     *
     * @return string
     */
    public function asPhone(?string $value): string
    {
        if (empty($value)) {
            return '';
        }

        $digits = preg_replace('/\D/', '', $value);

        if (!preg_match('/^7(\d{3})(\d{3})(\d{2})(\d{2})$/', $digits, $matches)) {
            return $value;
        }

        return sprintf(
            '+7 (%s) %s-%s-%s',
            $matches[1],
            $matches[2],
            $matches[3],
            $matches[4],
        );
    }
}
