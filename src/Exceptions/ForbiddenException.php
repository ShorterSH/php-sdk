<?php

declare(strict_types=1);

namespace Shorter\Sdk\Exceptions;

class ForbiddenException extends ShorterException
{
    public function __construct(string $message, string $code = 'FORBIDDEN')
    {
        parent::__construct($message, 403, $code);
    }
}
