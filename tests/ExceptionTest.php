<?php

declare(strict_types=1);

namespace Shorter\Sdk\Tests;

use PHPUnit\Framework\TestCase;
use Shorter\Sdk\Exceptions\AuthenticationException;
use Shorter\Sdk\Exceptions\ForbiddenException;
use Shorter\Sdk\Exceptions\NetworkException;
use Shorter\Sdk\Exceptions\NotFoundException;
use Shorter\Sdk\Exceptions\RateLimitException;
use Shorter\Sdk\Exceptions\ServerException;
use Shorter\Sdk\Exceptions\ShorterException;
use Shorter\Sdk\Exceptions\ValidationException;

class ExceptionTest extends TestCase
{
    public function testValidationException(): void
    {
        $e = new ValidationException('Invalid URL');
        $this->assertInstanceOf(ShorterException::class, $e);
        $this->assertSame(400, $e->status);
        $this->assertSame('VALIDATION_ERROR', $e->errorCode);
        $this->assertSame('Invalid URL', $e->getMessage());
    }

    public function testValidationExceptionCustomCode(): void
    {
        $e = new ValidationException('Bad input', 'CUSTOM_CODE');
        $this->assertSame('CUSTOM_CODE', $e->errorCode);
    }

    public function testAuthenticationException(): void
    {
        $e = new AuthenticationException('Not authenticated');
        $this->assertInstanceOf(ShorterException::class, $e);
        $this->assertSame(401, $e->status);
        $this->assertSame('AUTH_REQUIRED', $e->errorCode);
        $this->assertSame('Not authenticated', $e->getMessage());
    }

    public function testAuthenticationExceptionCustomCode(): void
    {
        $e = new AuthenticationException('Bad key', 'INVALID_API_KEY');
        $this->assertSame('INVALID_API_KEY', $e->errorCode);
    }

    public function testForbiddenException(): void
    {
        $e = new ForbiddenException('Access denied');
        $this->assertInstanceOf(ShorterException::class, $e);
        $this->assertSame(403, $e->status);
        $this->assertSame('FORBIDDEN', $e->errorCode);
        $this->assertSame('Access denied', $e->getMessage());
    }

    public function testNotFoundException(): void
    {
        $e = new NotFoundException('URL not found');
        $this->assertInstanceOf(ShorterException::class, $e);
        $this->assertSame(404, $e->status);
        $this->assertSame('NOT_FOUND', $e->errorCode);
        $this->assertSame('URL not found', $e->getMessage());
    }

    public function testRateLimitException(): void
    {
        $e = new RateLimitException('Too many requests');
        $this->assertInstanceOf(ShorterException::class, $e);
        $this->assertSame(429, $e->status);
        $this->assertSame('RATE_LIMITED', $e->errorCode);
        $this->assertSame('Too many requests', $e->getMessage());
    }

    public function testServerException(): void
    {
        $e = new ServerException('Internal error');
        $this->assertInstanceOf(ShorterException::class, $e);
        $this->assertSame(500, $e->status);
        $this->assertSame('SERVER_ERROR', $e->errorCode);
        $this->assertSame('Internal error', $e->getMessage());
    }

    public function testNetworkException(): void
    {
        $e = new NetworkException('Connection refused');
        $this->assertInstanceOf(ShorterException::class, $e);
        $this->assertSame(0, $e->status);
        $this->assertSame('NETWORK_ERROR', $e->errorCode);
        $this->assertSame('Connection refused', $e->getMessage());
    }

    public function testFromResponseMaps400(): void
    {
        $e = ShorterException::fromResponse(400, 'Bad request', 'VALIDATION_ERROR');
        $this->assertInstanceOf(ValidationException::class, $e);
    }

    public function testFromResponseMaps401(): void
    {
        $e = ShorterException::fromResponse(401, 'Unauthorized', 'AUTH_REQUIRED');
        $this->assertInstanceOf(AuthenticationException::class, $e);
    }

    public function testFromResponseMaps403(): void
    {
        $e = ShorterException::fromResponse(403, 'Forbidden', 'FORBIDDEN');
        $this->assertInstanceOf(ForbiddenException::class, $e);
    }

    public function testFromResponseMaps404(): void
    {
        $e = ShorterException::fromResponse(404, 'Not found', 'NOT_FOUND');
        $this->assertInstanceOf(NotFoundException::class, $e);
    }

    public function testFromResponseMaps429(): void
    {
        $e = ShorterException::fromResponse(429, 'Rate limited', 'RATE_LIMITED');
        $this->assertInstanceOf(RateLimitException::class, $e);
    }

    public function testFromResponseMaps500(): void
    {
        $e = ShorterException::fromResponse(500, 'Server error', 'SERVER_ERROR');
        $this->assertInstanceOf(ServerException::class, $e);
    }

    public function testFromResponseMaps502(): void
    {
        $e = ShorterException::fromResponse(502, 'Bad gateway', 'SERVER_ERROR');
        $this->assertInstanceOf(ServerException::class, $e);
    }

    public function testFromResponseMapsUnknownStatus(): void
    {
        $e = ShorterException::fromResponse(418, "I'm a teapot", 'TEAPOT');
        $this->assertInstanceOf(ShorterException::class, $e);
        $this->assertNotInstanceOf(ValidationException::class, $e);
        $this->assertNotInstanceOf(ServerException::class, $e);
        $this->assertSame(418, $e->status);
        $this->assertSame('TEAPOT', $e->errorCode);
    }
}
