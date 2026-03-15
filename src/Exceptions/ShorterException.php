<?php

declare(strict_types=1);

namespace Shorter\Sdk\Exceptions;

class ShorterException extends \RuntimeException
{
    public readonly int $status;
    public readonly string $errorCode;

    public function __construct(string $message, int $status = 0, string $code = 'UNKNOWN_ERROR')
    {
        parent::__construct($message);
        $this->status = $status;
        $this->errorCode = $code;
    }

    public static function fromResponse(int $status, string $message, string $code): self
    {
        return match (true) {
            $status === 400 => new ValidationException($message, $code),
            $status === 401 => new AuthenticationException($message, $code),
            $status === 403 => new ForbiddenException($message, $code),
            $status === 404 => new NotFoundException($message, $code),
            $status === 429 => new RateLimitException($message, $code),
            $status >= 500 => new ServerException($message, $code),
            default => new self($message, $status, $code),
        };
    }
}
