<?php

declare(strict_types=1);

namespace Shorter\Sdk\Data;

class OverviewAnalyticsResult
{
    public function __construct(
        public readonly int $total_clicks,
        public readonly ?int $unique_visitors,
        public readonly int $prev_period_clicks,
        public readonly ?int $prev_period_unique,
        public readonly TimeSeries $timeseries,
        /** @var TopUrl[] */
        public readonly array $top_urls,
        /** @var BreakdownItem[] */
        public readonly array $country_breakdown,
        /** @var BreakdownItem[] */
        public readonly array $device_breakdown,
        /** @var BreakdownItem[] */
        public readonly array $browser_breakdown,
        /** @var BreakdownItem[] */
        public readonly array $os_breakdown,
        /** @var BreakdownItem[] */
        public readonly array $referrer_breakdown,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            total_clicks: $data['totalClicks'],
            unique_visitors: $data['uniqueVisitors'] ?? null,
            prev_period_clicks: $data['prevPeriodClicks'],
            prev_period_unique: $data['prevPeriodUnique'] ?? null,
            timeseries: TimeSeries::fromArray($data['timeseries']),
            top_urls: array_map(TopUrl::fromArray(...), $data['topUrls']),
            country_breakdown: array_map(BreakdownItem::fromArray(...), $data['countryBreakdown']),
            device_breakdown: array_map(BreakdownItem::fromArray(...), $data['deviceBreakdown']),
            browser_breakdown: array_map(BreakdownItem::fromArray(...), $data['browserBreakdown']),
            os_breakdown: array_map(BreakdownItem::fromArray(...), $data['osBreakdown']),
            referrer_breakdown: array_map(BreakdownItem::fromArray(...), $data['referrerBreakdown']),
        );
    }
}
