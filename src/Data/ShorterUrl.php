<?php

declare(strict_types=1);

namespace Shorter\Sdk\Data;

class ShorterUrl
{
    public function __construct(
        public readonly int $id,
        public readonly string $short_code,
        public readonly string $short_url,
        public readonly string $original_url,
        public readonly int $click_count,
        public readonly string $created_at,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            short_code: $data['short_code'],
            short_url: $data['short_url'],
            original_url: $data['original_url'],
            click_count: $data['click_count'],
            created_at: date('c', (int) ($data['created_at'] / 1000)),
        );
    }
}
