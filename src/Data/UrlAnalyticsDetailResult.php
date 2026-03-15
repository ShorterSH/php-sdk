<?php

declare(strict_types=1);

namespace Shorter\Sdk\Data;

class UrlAnalyticsDetailResult
{
    public function __construct(
        public readonly ShortenResult $url,
        public readonly AnalyticsSummary $summary,
        public readonly TimeSeries $timeseries,
        /** @var array<string, Breakdown> */
        public readonly array $breakdowns,
    ) {}

    public static function fromArray(array $data): self
    {
        $breakdowns = [];
        foreach ($data['breakdowns'] as $dimension => $bd) {
            $breakdowns[$dimension] = new Breakdown(
                dimension: $dimension,
                total: $bd['total'],
                data: array_map(BreakdownItem::fromArray(...), $bd['data']),
            );
        }

        return new self(
            url: ShortenResult::fromArray($data['url']),
            summary: AnalyticsSummary::fromArray($data['summary']),
            timeseries: TimeSeries::fromArray($data['timeseries']),
            breakdowns: $breakdowns,
        );
    }
}
