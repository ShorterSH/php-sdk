<?php

declare(strict_types=1);

namespace Shorter\Sdk;

use Shorter\Sdk\Exceptions\ValidationException;

final class Validation
{
    private const SHORT_CODE_PATTERN = '/^[a-zA-Z0-9]{6}$/';

    public static function pathShortCode(string $short_code): string
    {
        if (!preg_match(self::SHORT_CODE_PATTERN, $short_code)) {
            throw new ValidationException('Invalid short code format. Short codes must be 6 alphanumeric characters.', 'INVALID_SHORT_CODE');
        }

        return rawurlencode($short_code);
    }

    public static function pagination(?int $page, ?int $limit): void
    {
        if ($page !== null && $page < 1) {
            throw new ValidationException('page must be a positive integer', 'INVALID_PAGINATION');
        }
        if ($limit !== null && $limit < 1) {
            throw new ValidationException('limit must be a positive integer', 'INVALID_PAGINATION');
        }
        if ($limit !== null && $limit > 100) {
            throw new ValidationException('limit must be <= 100', 'INVALID_PAGINATION');
        }
    }
}
