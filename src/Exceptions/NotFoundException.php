<?php

declare(strict_types=1);

namespace Shorter\Sdk\Exceptions;

class NotFoundException extends ShorterException
{
    public function __construct(string $message, string $code = 'NOT_FOUND')
    {
        parent::__construct($message, 404, $code);
    }
}
