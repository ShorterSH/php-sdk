<?php

declare(strict_types=1);

namespace Shorter\Sdk;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Shorter\Sdk\Exceptions\NetworkException;
use Shorter\Sdk\Exceptions\ShorterException;

class HttpClient
{
    private const REQUEST_TIMEOUT_SECONDS = 10;
    private const CONNECT_TIMEOUT_SECONDS = 5;
    private readonly string $base_url;

    public function __construct(
        string $base_url,
        private readonly string $api_key,
        private readonly ClientInterface $client,
    ) {
        $this->base_url = rtrim($base_url, '/');
    }

    public function request(string $method, string $path, array $params = [], ?array $body = null): array
    {
        $url = $this->base_url . $path;

        $filtered = array_filter($params, fn ($v) => $v !== null);
        if ($filtered) {
            $url .= '?' . http_build_query($filtered);
        }

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Accept' => 'application/json',
            ],
            'http_errors' => false,
            'timeout' => self::REQUEST_TIMEOUT_SECONDS,
            'connect_timeout' => self::CONNECT_TIMEOUT_SECONDS,
        ];

        if ($body !== null) {
            $options['headers']['Content-Type'] = 'application/json';
            $options['body'] = json_encode($body);
        }

        try {
            $response = $this->client->request($method, $url, $options);
        } catch (GuzzleException $e) {
            throw new NetworkException($e->getMessage());
        }

        $status = $response->getStatusCode();
        $responseBody = (string) $response->getBody();

        if ($responseBody === '') {
            // Empty bodies are only acceptable on DELETE; everything else expects JSON.
            if (strtoupper($method) === 'DELETE' && $status >= 200 && $status < 300) {
                return [];
            }
            throw ShorterException::fromResponse(
                $status,
                "Empty response body from server",
                'EMPTY_RESPONSE',
            );
        }

        $data = json_decode($responseBody, true);

        if (!is_array($data)) {
            throw ShorterException::fromResponse(
                $status,
                "Invalid JSON response from server",
                'INVALID_RESPONSE',
            );
        }

        // Treat missing `success` as success when status is 2xx (some endpoints omit it).
        $hasSuccessField = array_key_exists('success', $data);
        $explicitFailure = $hasSuccessField && $data['success'] === false;
        if ($status < 200 || $status >= 300 || $explicitFailure) {
            $message = $data['message'] ?? "Request failed with status {$status}";
            $code = $data['code'] ?? 'UNKNOWN_ERROR';
            throw ShorterException::fromResponse($status, $message, $code);
        }

        return $data;
    }
}
