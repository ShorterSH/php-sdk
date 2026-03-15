<?php

declare(strict_types=1);

namespace Shorter\Sdk\Exceptions;

class AuthenticationException extends ShorterException
{
    public function __construct(string $message, string $code = 'AUTH_REQUIRED')
    {
        parent::__construct($message, 401, $code);
    }
}
