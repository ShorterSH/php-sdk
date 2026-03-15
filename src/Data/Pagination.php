<?php

declare(strict_types=1);

namespace Shorter\Sdk\Data;

class Pagination
{
    public function __construct(
        public readonly int $page,
        public readonly int $limit,
        public readonly int $total,
        public readonly int $total_pages,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            page: $data['page'],
            limit: $data['limit'],
            total: $data['total'],
            total_pages: $data['totalPages'],
        );
    }
}
