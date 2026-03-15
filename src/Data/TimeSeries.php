<?php

declare(strict_types=1);

namespace Shorter\Sdk\Data;

class TimeSeries
{
    public function __construct(
        public readonly string $granularity,
        /** @var TimeSeriesPoint[] */
        public readonly array $data,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            granularity: $data['granularity'],
            data: array_map(TimeSeriesPoint::fromArray(...), $data['data']),
        );
    }
}
