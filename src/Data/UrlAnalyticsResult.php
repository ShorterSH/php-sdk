<?php

declare(strict_types=1);

namespace Shorter\Sdk\Data;

class UrlAnalyticsResult
{
    public function __construct(
        public readonly AnalyticsSummary $summary,
        public readonly TimeSeries $timeseries,
        public readonly ?Breakdown $breakdown,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            summary: AnalyticsSummary::fromArray($data['summary']),
            timeseries: TimeSeries::fromArray($data['timeseries']),
            breakdown: isset($data['breakdown']) ? Breakdown::fromArray($data['breakdown']) : null,
        );
    }
}
