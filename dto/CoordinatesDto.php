<?php

declare(strict_types=1);

namespace app\dto;

/**
 * Geographic coordinates returned by a geocoder.
 */
final readonly class CoordinatesDto
{
    /**
     * Initializes geographic coordinates.
     */
    public function __construct(
        public float $latitude,
        public float $longitude,
    ) {
    }
}
