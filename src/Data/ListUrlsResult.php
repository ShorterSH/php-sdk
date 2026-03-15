<?php

declare(strict_types=1);

namespace Shorter\Sdk\Data;

class ListUrlsResult
{
    public function __construct(
        /** @var ShorterUrl[] */
        public readonly array $urls,
        public readonly Pagination $pagination,
        public readonly int $total_clicks,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            urls: array_map(ShorterUrl::fromArray(...), $data['data']),
            pagination: Pagination::fromArray($data['pagination']),
            total_clicks: $data['totalClicks'],
        );
    }
}
