<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\services\geocoding;

use JsonException;
use Sanweb\Taskforce\dto\CoordinatesDto;
use Sanweb\Taskforce\services\http\HttpClientException;
use Sanweb\Taskforce\services\http\HttpClientInterface;

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
     * Returns address variants suitable for an autocomplete field.
     *
     * @return list<array{value: string, latitude: float, longitude: float}>
     *
     * @throws GeocodingException
     */
    public function suggest(string $query): array
    {
        $data = $this->request($query);
        $members = $data['response']['GeoObjectCollection']['featureMember'] ?? [];

        if (!is_array($members)) {
            return [];
        }

        $suggestions = [];

        foreach ($members as $member) {
            $geoObject = $member['GeoObject'] ?? null;
            $address = $geoObject['metaDataProperty']['GeocoderMetaData']['text'] ?? null;
            $coordinates = $this->parsePosition($geoObject['Point']['pos'] ?? null);

            if (!is_string($address) || $address === '' || $coordinates === null) {
                continue;
            }

            $suggestions[] = [
                'value' => $address,
                'latitude' => $coordinates->latitude,
                'longitude' => $coordinates->longitude,
            ];
        }

        return $suggestions;
    }

    /**
     * Sends a request to the geocoder and decodes its response.
     *
     * @return array<string, mixed>
     */
    private function request(string $query): array
    {
        if ($this->apiKey === '') {
            throw new GeocodingException('Не задан API-ключ геокодера.');
        }

        try {
            $body = $this->httpClient->get($this->endpoint, [
                'apikey' => $this->apiKey,
                'geocode' => $query,
                'format' => 'json',
                'results' => 5,
            ]);
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (HttpClientException | JsonException $exception) {
            throw new GeocodingException(
                'Не удалось получить варианты адреса.',
                0,
                $exception,
            );
        }

        return is_array($data) ? $data : [];
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
