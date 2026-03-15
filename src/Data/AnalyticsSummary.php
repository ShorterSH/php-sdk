<?php

declare(strict_types=1);

namespace Shorter\Sdk\Data;

class AnalyticsSummary
{
    public function __construct(
        public readonly int $total_clicks,
        public readonly ?int $unique_visitors,
        public readonly int $prev_period_clicks,
        public readonly ?int $prev_period_unique,
        public readonly ?string $top_country,
        public readonly ?string $top_referrer,
        public readonly ?string $top_device,
        public readonly ?string $top_browser,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            total_clicks: $data['totalClicks'],
            unique_visitors: $data['uniqueVisitors'] ?? null,
            prev_period_clicks: $data['prevPeriodClicks'],
            prev_period_unique: $data['prevPeriodUnique'] ?? null,
            top_country: $data['topCountry'] ?? null,
            top_referrer: $data['topReferrer'] ?? null,
            top_device: $data['topDevice'] ?? null,
            top_browser: $data['topBrowser'] ?? null,
        );
    }
}
