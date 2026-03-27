<?php

declare(strict_types=1);

namespace Shorter\Sdk;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Shorter\Sdk\Data\DeleteResult;
use Shorter\Sdk\Data\ListUrlsResult;
use Shorter\Sdk\Data\ShortenResult;
use Shorter\Sdk\Exceptions\AuthenticationException;

class ShorterClient
{
    private const API_KEY_PATTERN = '/^sk_[a-f0-9]{64}$/';
    public readonly AnalyticsClient $analytics;
    private readonly HttpClient $http;

    public function __construct(
        ?string $api_key = null,
        ?string $base_url = null,
        ?ClientInterface $http_client = null,
    ) {
        $api_key = $api_key ?? getenv('SHORTER_API_KEY') ?: null;

        if ($api_key === null) {
            throw new AuthenticationException('API key is required. Pass it as a parameter or set the SHORTER_API_KEY environment variable.', 'AUTH_REQUIRED');
        }

        if (!preg_match(self::API_KEY_PATTERN, $api_key)) {
            throw new AuthenticationException('Invalid API key format. API key must match "sk_" followed by 64 lowercase hex characters.', 'INVALID_API_KEY');
        }

        $base_url = $base_url ?? 'https://shorter.sh';

        $this->http = new HttpClient(
            $base_url,
            $api_key,
            $http_client ?? new Client(),
        );

        $this->analytics = new AnalyticsClient($this->http);
    }

    public function shorten(string $url): ShortenResult
    {
        $data = $this->http->request('POST', '/api/v1/shorten', [], ['url' => $url]);

        return ShortenResult::fromArray($data);
    }

    public function list(?int $page = null, ?int $limit = null): ListUrlsResult
    {
        $params = [
            'page' => $page !== null ? (string) $page : null,
            'limit' => $limit !== null ? (string) $limit : null,
        ];

        $data = $this->http->request('GET', '/api/v1/urls', $params);

        return ListUrlsResult::fromArray($data);
    }

    public function delete(string $short_code): DeleteResult
    {
        $data = $this->http->request('DELETE', "/api/v1/urls/{$short_code}");

        return DeleteResult::fromArray($data);
    }
}
