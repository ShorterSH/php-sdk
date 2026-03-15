<?php

declare(strict_types=1);

namespace Shorter\Sdk\Exceptions;

class RateLimitException extends ShorterException
{
    public function __construct(string $message, string $code = 'RATE_LIMITED')
    {
        parent::__construct($message, 429, $code);
    }
}
