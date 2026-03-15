<?php

declare(strict_types=1);

namespace Shorter\Sdk\Data;

class BreakdownItem
{
    public function __construct(
        public readonly string $value,
        public readonly int $clicks,
        public readonly float $percentage,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            value: $data['value'],
            clicks: $data['clicks'],
            percentage: (float) $data['percentage'],
        );
    }
}
