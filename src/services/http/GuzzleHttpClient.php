<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\services\http;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * HTTP client for JSON API requests backed by Guzzle.
 */
final class GuzzleHttpClient implements HttpClientInterface
{
    private readonly ClientInterface $client;

    /**
     * Creates an HTTP client with request timeouts.
     */
    public function __construct(
        ?ClientInterface $client = null,
        private readonly float $connectTimeout = 5.0,
        private readonly float $timeout = 10.0,
    ) {
        $this->client = $client ?? new Client();
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $url, array $query = []): string
    {
        try {
            $response = $this->client->request('GET', $url, [
                'allow_redirects' => false,
                'connect_timeout' => $this->connectTimeout,
                'headers' => ['Accept' => 'application/json'],
                'http_errors' => true,
                'query' => $query,
                'timeout' => $this->timeout,
            ]);
        } catch (GuzzleException $exception) {
            throw new HttpClientException(
                'HTTP-запрос завершился с ошибкой.',
                0,
                $exception,
            );
        }

        return (string) $response->getBody();
    }
}
