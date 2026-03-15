<?php

declare(strict_types=1);

namespace Shorter\Sdk\Data;

class Breakdown
{
    public function __construct(
        public readonly string $dimension,
        public readonly int $total,
        /** @var BreakdownItem[] */
        public readonly array $data,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            dimension: $data['dimension'],
            total: $data['total'],
            data: array_map(BreakdownItem::fromArray(...), $data['data']),
        );
    }
}
