<?php

declare(strict_types=1);

namespace Shorter\Sdk\Data;

class TimeSeriesPoint
{
    public function __construct(
        public readonly int $period,
        public readonly int $clicks,
        public readonly ?int $unique_visitors,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            period: $data['period'],
            clicks: $data['clicks'],
            unique_visitors: $data['unique_visitors'] ?? null,
        );
    }
}
