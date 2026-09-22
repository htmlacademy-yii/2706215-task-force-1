<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\dto;

/**
 * Geographic coordinates returned by a geocoder.
 */
final readonly class CoordinatesDto
{
    /**
     * Initializes geographic coordinates.
     *
     * @param float $latitude
     * @param float $longitude
     */
    public function __construct(
        public float $latitude,
        public float $longitude,
    ) {}
}
