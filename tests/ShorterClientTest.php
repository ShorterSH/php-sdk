<?php

declare(strict_types=1);

namespace Shorter\Sdk\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Shorter\Sdk\Exceptions\AuthenticationException;
use Shorter\Sdk\Exceptions\NetworkException;
use Shorter\Sdk\Exceptions\NotFoundException;
use Shorter\Sdk\Exceptions\ShorterException;
use Shorter\Sdk\Exceptions\ValidationException;
use Shorter\Sdk\ShorterClient;

class ShorterClientTest extends TestCase
{
    private const TEST_KEY = 'sk_' . 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';

    private function createClient(array $responses, array &$history = []): ShorterClient
    {
        $mock = new MockHandler($responses);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($history));
        $httpClient = new Client(['handler' => $stack]);

        return new ShorterClient(
            api_key: self::TEST_KEY,
            base_url: 'https://shorter.sh',
            http_client: $httpClient,
        );
    }

    public function testConstructorThrowsWithoutApiKey(): void
    {
        // Ensure env var is not set
        putenv('SHORTER_API_KEY');

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('API key is required');
        new ShorterClient();
    }

    public function testConstructorThrowsForInvalidKeyFormat(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid API key format');
        new ShorterClient(api_key: 'invalid_key');
    }

    public function testConstructorAcceptsValidKey(): void
    {
        $history = [];
        $client = $this->createClient([
            new Response(200, [], json_encode([
                'shortCode' => 'abc123',
                'shortUrl' => 'https://shorter.sh/abc123',
                'originalUrl' => 'https://example.com',
            ])),
        ], $history);

        $this->assertNotNull($client);
    }

    public function testShorten(): void
    {
        $history = [];
        $client = $this->createClient([
            new Response(200, [], json_encode([
                'shortCode' => 'abc123',
                'shortUrl' => 'https://shorter.sh/abc123',
                'originalUrl' => 'https://example.com',
            ])),
        ], $history);

        $result = $client->shorten('https://example.com');

        $this->assertSame('abc123', $result->short_code);
        $this->assertSame('https://shorter.sh/abc123', $result->short_url);
        $this->assertSame('https://example.com', $result->original_url);

        // Verify POST request
        $request = $history[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertStringContainsString('/api/v1/shorten', (string) $request->getUri());
        $body = json_decode((string) $request->getBody(), true);
        $this->assertSame('https://example.com', $body['url']);
    }

    public function testList(): void
    {
        $history = [];
        $client = $this->createClient([
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'id' => 1,
                        'short_code' => 'abc123',
                        'short_url' => 'https://shorter.sh/abc123',
                        'original_url' => 'https://example.com',
                        'click_count' => 42,
                        'created_at' => 1700000000000,
                    ],
                ],
                'pagination' => [
                    'page' => 1,
                    'limit' => 10,
                    'total' => 1,
                    'totalPages' => 1,
                ],
                'totalClicks' => 42,
            ])),
        ], $history);

        $result = $client->list();

        $this->assertCount(1, $result->urls);
        $this->assertSame('abc123', $result->urls[0]->short_code);
        $this->assertSame('https://shorter.sh/abc123', $result->urls[0]->short_url);
        $this->assertSame('https://example.com', $result->urls[0]->original_url);
        $this->assertSame(42, $result->urls[0]->click_count);
        // created_at: epoch ms 1700000000000 → ISO 8601
        $this->assertStringContainsString('2023-11-14', $result->urls[0]->created_at);

        $this->assertSame(1, $result->pagination->page);
        $this->assertSame(10, $result->pagination->limit);
        $this->assertSame(1, $result->pagination->total);
        $this->assertSame(1, $result->pagination->total_pages);
        $this->assertSame(42, $result->total_clicks);
    }

    public function testListWithPageAndLimit(): void
    {
        $history = [];
        $client = $this->createClient([
            new Response(200, [], json_encode([
                'data' => [],
                'pagination' => [
                    'page' => 2,
                    'limit' => 5,
                    'total' => 15,
                    'totalPages' => 3,
                ],
                'totalClicks' => 100,
            ])),
        ], $history);

        $client->list(page: 2, limit: 5);

        $request = $history[0]['request'];
        $query = $request->getUri()->getQuery();
        $this->assertStringContainsString('page=2', $query);
        $this->assertStringContainsString('limit=5', $query);
    }

    public function testDelete(): void
    {
        $history = [];
        $client = $this->createClient([
            new Response(200, [], json_encode([
                'message' => 'URL deleted successfully',
            ])),
        ], $history);

        $result = $client->delete('abc123');

        $this->assertSame('URL deleted successfully', $result->message);

        $request = $history[0]['request'];
        $this->assertSame('DELETE', $request->getMethod());
        $this->assertStringContainsString('/api/v1/urls/abc123', (string) $request->getUri());
    }

    public function testDeleteNotFound(): void
    {
        $client = $this->createClient([
            new Response(404, [], json_encode([
                'success' => false,
                'message' => 'URL not found',
                'code' => 'NOT_FOUND',
            ])),
        ]);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('URL not found');
        $client->delete('nonexistent');
    }

    public function testShortenValidationError(): void
    {
        $client = $this->createClient([
            new Response(400, [], json_encode([
                'success' => false,
                'message' => 'Invalid URL provided',
                'code' => 'VALIDATION_ERROR',
            ])),
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid URL provided');
        $client->shorten('not-a-url');
    }

    public function testNetworkError(): void
    {
        $mock = new MockHandler([
            new ConnectException('Connection refused', new Request('GET', '/api/v1/urls')),
        ]);
        $stack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $stack]);

        $client = new ShorterClient(
            api_key: self::TEST_KEY,
            base_url: 'https://shorter.sh',
            http_client: $httpClient,
        );

        $this->expectException(NetworkException::class);
        $client->list();
    }

    public function testBearerAuthHeader(): void
    {
        $history = [];
        $client = $this->createClient([
            new Response(200, [], json_encode([
                'shortCode' => 'abc123',
                'shortUrl' => 'https://shorter.sh/abc123',
                'originalUrl' => 'https://example.com',
            ])),
        ], $history);

        $client->shorten('https://example.com');

        $request = $history[0]['request'];
        $this->assertSame('Bearer ' . self::TEST_KEY, $request->getHeaderLine('Authorization'));
    }

    public function testInvalidJsonResponse(): void
    {
        $client = $this->createClient([
            new Response(200, [], 'not json'),
        ]);

        $this->expectException(ShorterException::class);
        $this->expectExceptionMessage('Invalid JSON response from server');
        $client->list();
    }

    public function testSuccessFalseWith200(): void
    {
        $client = $this->createClient([
            new Response(200, [], json_encode([
                'success' => false,
                'message' => 'Something went wrong',
                'code' => 'SOME_ERROR',
            ])),
        ]);

        $this->expectException(ShorterException::class);
        $this->expectExceptionMessage('Something went wrong');
        $client->shorten('https://example.com');
    }

    public function testConstructorReadsEnvVar(): void
    {
        $envKey = 'sk_' . str_repeat('b', 64);
        putenv("SHORTER_API_KEY={$envKey}");

        try {
            $mock = new MockHandler([
                new Response(200, [], json_encode([
                    'shortCode' => 'abc123',
                    'shortUrl' => 'https://shorter.sh/abc123',
                    'originalUrl' => 'https://example.com',
                ])),
            ]);
            $history = [];
            $stack = HandlerStack::create($mock);
            $stack->push(Middleware::history($history));
            $httpClient = new Client(['handler' => $stack]);

            $client = new ShorterClient(http_client: $httpClient);
            $client->shorten('https://example.com');

            $request = $history[0]['request'];
            $this->assertSame("Bearer {$envKey}", $request->getHeaderLine('Authorization'));
        } finally {
            putenv('SHORTER_API_KEY');
        }
    }
}
