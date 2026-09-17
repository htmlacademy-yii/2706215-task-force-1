<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\services\geocoding;

use Sanweb\Taskforce\dto\CoordinatesDto;

interface GeocoderInterface
{
    /**
     * Finds coordinates for an address.
     *
     * @throws GeocodingException
     */
    public function geocode(string $address): ?CoordinatesDto;
}
