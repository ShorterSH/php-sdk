<?php

declare(strict_types=1);

namespace Shorter\Sdk\Exceptions;

class ValidationException extends ShorterException
{
    public function __construct(string $message, string $code = 'VALIDATION_ERROR')
    {
        parent::__construct($message, 400, $code);
    }
}
