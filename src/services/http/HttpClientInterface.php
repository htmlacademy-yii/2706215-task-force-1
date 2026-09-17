<?php

declare(strict_types=1);

namespace Sanweb\Taskforce\services\http;

interface HttpClientInterface
{
    /**
     * Sends a GET request and returns its response body.
     *
     * @param array<string, scalar> $query
     *
     * @throws HttpClientException
     */
    public function get(string $url, array $query = []): string;
}
