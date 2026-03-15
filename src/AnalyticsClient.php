<?php

declare(strict_types=1);

namespace Shorter\Sdk;

use Shorter\Sdk\Data\OverviewAnalyticsResult;
use Shorter\Sdk\Data\UrlAnalyticsDetailResult;
use Shorter\Sdk\Data\UrlAnalyticsResult;

class AnalyticsClient
{
    public function __construct(private readonly HttpClient $http) {}

    public function overview(
        string|int|null $start = null,
        string|int|null $end = null,
    ): OverviewAnalyticsResult {
        $params = [
            'start' => $start !== null ? (string) $start : null,
            'end' => $end !== null ? (string) $end : null,
        ];

        $data = $this->http->request('GET', '/api/v1/analytics/overview', $params);

        return OverviewAnalyticsResult::fromArray($data);
    }

    public function url(
        string $short_code,
        string|int|null $start = null,
        string|int|null $end = null,
        ?string $dimension = null,
        ?int $limit = null,
        bool $detail = false,
    ): UrlAnalyticsResult|UrlAnalyticsDetailResult {
        $params = [
            'start' => $start !== null ? (string) $start : null,
            'end' => $end !== null ? (string) $end : null,
            'dimension' => $dimension,
            'limit' => $limit !== null ? (string) $limit : null,
            'detail' => $detail ? 'true' : null,
        ];

        $data = $this->http->request('GET', "/api/v1/analytics/{$short_code}", $params);

        if ($detail) {
            return UrlAnalyticsDetailResult::fromArray($data);
        }

        return UrlAnalyticsResult::fromArray($data);
    }
}
