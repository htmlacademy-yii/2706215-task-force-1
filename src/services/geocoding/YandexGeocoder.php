<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\services\geocoding;

use Sanweb\Taskforce\dto\CoordinatesDto;
use Sanweb\Taskforce\services\http\HttpClientException;
use Sanweb\Taskforce\services\http\HttpClientInterface;
use JsonException;

/**
 * Resolves addresses through the Yandex Geocoder HTTP API.
 */
final class YandexGeocoder implements GeocoderInterface
{
    private const DEFAULT_ENDPOINT = 'https://geocode-maps.yandex.ru/1.x/';

    /**
     * Creates a Yandex geocoder client.
     */
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey,
        private readonly string $endpoint = self::DEFAULT_ENDPOINT,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function geocode(string $address): ?CoordinatesDto
    {
        if ($this->apiKey === '') {
            throw new GeocodingException('Не задан API-ключ геокодера.');
        }

        try {
            $body = $this->httpClient->get($this->endpoint, [
                'apikey' => $this->apiKey,
                'geocode' => $address,
                'format' => 'json',
                'results' => 1,
            ]);
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (HttpClientException | JsonException $exception) {
            throw new GeocodingException(
                'Не удалось получить координаты адреса.',
                0,
                $exception,
            );
        }

        $position = $data['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos'] ?? null;

        return $this->parsePosition($position);
    }

    /**
     * Converts a Yandex longitude-latitude string into coordinates.
     */
    private function parsePosition(mixed $position): ?CoordinatesDto
    {
        if (!is_string($position)) {
            return null;
        }

        $parts = preg_split('/\s+/', trim($position));

        if ($parts === false || count($parts) !== 2) {
            return null;
        }

        if (!is_numeric($parts[0]) || !is_numeric($parts[1])) {
            return null;
        }

        return new CoordinatesDto(
            latitude: (float) $parts[1],
            longitude: (float) $parts[0],
        );
    }
}
