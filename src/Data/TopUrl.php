<?php

declare(strict_types=1);

namespace Shorter\Sdk\Data;

class TopUrl
{
    public function __construct(
        public readonly string $short_code,
        public readonly string $short_url,
        public readonly string $original_url,
        public readonly int $clicks,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            short_code: $data['short_code'],
            short_url: $data['short_url'],
            original_url: $data['original_url'],
            clicks: $data['clicks'],
        );
    }
}
