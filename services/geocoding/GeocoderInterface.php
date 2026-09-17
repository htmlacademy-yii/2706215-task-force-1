<?php

declare(strict_types=1);

namespace app\services\geocoding;

use app\dto\CoordinatesDto;

interface GeocoderInterface
{
    /**
     * Finds coordinates for an address.
     *
     * @throws GeocodingException
     */
    public function geocode(string $address): ?CoordinatesDto;
}
