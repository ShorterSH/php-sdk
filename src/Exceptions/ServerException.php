<?php

declare(strict_types=1);

namespace Shorter\Sdk\Exceptions;

class ServerException extends ShorterException
{
    public function __construct(string $message, string $code = 'SERVER_ERROR')
    {
        parent::__construct($message, 500, $code);
    }
}
