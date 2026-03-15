<?php

declare(strict_types=1);

namespace Shorter\Sdk\Exceptions;

class NetworkException extends ShorterException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 0, 'NETWORK_ERROR');
    }
}
