<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\services\geocoding;

interface GeocoderInterface
{
    /**
     * Returns address variants for an autocomplete field.
     *
     * @return list<array{value: string, latitude: float, longitude: float}>
     *
     * @throws GeocodingException
     */
    public function suggest(string $query): array;
}
