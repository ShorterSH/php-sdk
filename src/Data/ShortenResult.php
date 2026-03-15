<?php

declare(strict_types=1);

namespace Shorter\Sdk\Data;

class ShortenResult
{
    public function __construct(
        public readonly string $short_code,
        public readonly string $short_url,
        public readonly string $original_url,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            short_code: $data['shortCode'],
            short_url: $data['shortUrl'],
            original_url: $data['originalUrl'],
        );
    }
}
