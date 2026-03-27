<?php

declare(strict_types=1);

namespace Shorter\Sdk\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Shorter\Sdk\Data\UrlAnalyticsDetailResult;
use Shorter\Sdk\Data\UrlAnalyticsResult;
use Shorter\Sdk\ShorterClient;

class AnalyticsClientTest extends TestCase
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

    private function overviewResponse(): array
    {
        return [
            'totalClicks' => 1500,
            'uniqueVisitors' => 1200,
            'prevPeriodClicks' => 1000,
            'prevPeriodUnique' => 800,
            'timeseries' => [
                'granularity' => 'day',
                'data' => [
                    ['period' => 1700000000, 'clicks' => 100, 'unique_visitors' => 80],
                    ['period' => 1700086400, 'clicks' => 150, 'unique_visitors' => 120],
                ],
            ],
            'topUrls' => [
                [
                    'short_code' => 'abc123',
                    'short_url' => 'https://shorter.sh/abc123',
                    'original_url' => 'https://example.com',
                    'clicks' => 500,
                ],
            ],
            'countryBreakdown' => [
                ['value' => 'US', 'clicks' => 800, 'percentage' => 53.3],
            ],
            'deviceBreakdown' => [
                ['value' => 'Desktop', 'clicks' => 900, 'percentage' => 60.0],
            ],
            'browserBreakdown' => [
                ['value' => 'Chrome', 'clicks' => 700, 'percentage' => 46.7],
            ],
            'osBreakdown' => [
                ['value' => 'Windows', 'clicks' => 600, 'percentage' => 40.0],
            ],
            'referrerBreakdown' => [
                ['value' => 'google.com', 'clicks' => 400, 'percentage' => 26.7],
            ],
        ];
    }

    public function testOverview(): void
    {
        $history = [];
        $client = $this->createClient([
            new Response(200, [], json_encode($this->overviewResponse())),
        ], $history);

        $result = $client->analytics->overview();

        $this->assertSame(1500, $result->total_clicks);
        $this->assertSame(1200, $result->unique_visitors);
        $this->assertSame(1000, $result->prev_period_clicks);
        $this->assertSame(800, $result->prev_period_unique);

        // Timeseries
        $this->assertSame('day', $result->timeseries->granularity);
        $this->assertCount(2, $result->timeseries->data);
        $this->assertSame(80, $result->timeseries->data[0]->unique_visitors);
        $this->assertSame(120, $result->timeseries->data[1]->unique_visitors);

        // Top URLs
        $this->assertCount(1, $result->top_urls);
        $this->assertSame('abc123', $result->top_urls[0]->short_code);
        $this->assertSame('https://shorter.sh/abc123', $result->top_urls[0]->short_url);
        $this->assertSame('https://example.com', $result->top_urls[0]->original_url);
        $this->assertSame(500, $result->top_urls[0]->clicks);

        // Breakdowns
        $this->assertCount(1, $result->country_breakdown);
        $this->assertSame('US', $result->country_breakdown[0]->value);
        $this->assertCount(1, $result->device_breakdown);
        $this->assertCount(1, $result->browser_breakdown);
        $this->assertCount(1, $result->os_breakdown);
        $this->assertCount(1, $result->referrer_breakdown);
    }

    public function testOverviewWithStartEnd(): void
    {
        $history = [];
        $client = $this->createClient([
            new Response(200, [], json_encode($this->overviewResponse())),
        ], $history);

        $client->analytics->overview(start: 1700000000, end: 1700086400);

        $request = $history[0]['request'];
        $query = $request->getUri()->getQuery();
        $this->assertStringContainsString('start=1700000000', $query);
        $this->assertStringContainsString('end=1700086400', $query);
    }

    public function testUrlBasic(): void
    {
        $history = [];
        $client = $this->createClient([
            new Response(200, [], json_encode([
                'summary' => [
                    'totalClicks' => 500,
                    'uniqueVisitors' => 400,
                    'prevPeriodClicks' => 300,
                    'prevPeriodUnique' => 250,
                    'topCountry' => 'US',
                    'topReferrer' => 'google.com',
                    'topDevice' => 'Desktop',
                    'topBrowser' => 'Chrome',
                ],
                'timeseries' => [
                    'granularity' => 'day',
                    'data' => [
                        ['period' => 1700000000, 'clicks' => 50, 'unique_visitors' => 40],
                    ],
                ],
            ])),
        ], $history);

        $result = $client->analytics->url('abc123');

        $this->assertInstanceOf(UrlAnalyticsResult::class, $result);
        $this->assertSame(500, $result->summary->total_clicks);
        $this->assertSame(400, $result->summary->unique_visitors);
        $this->assertSame('US', $result->summary->top_country);
        $this->assertSame('day', $result->timeseries->granularity);
        $this->assertCount(1, $result->timeseries->data);
        $this->assertNull($result->breakdown);
    }

    public function testUrlWithDimension(): void
    {
        $history = [];
        $client = $this->createClient([
            new Response(200, [], json_encode([
                'summary' => [
                    'totalClicks' => 500,
                    'uniqueVisitors' => 400,
                    'prevPeriodClicks' => 300,
                    'prevPeriodUnique' => 250,
                    'topCountry' => 'US',
                    'topReferrer' => 'google.com',
                    'topDevice' => 'Desktop',
                    'topBrowser' => 'Chrome',
                ],
                'timeseries' => [
                    'granularity' => 'day',
                    'data' => [],
                ],
                'breakdown' => [
                    'dimension' => 'country',
                    'total' => 500,
                    'data' => [
                        ['value' => 'US', 'clicks' => 300, 'percentage' => 60.0],
                        ['value' => 'UK', 'clicks' => 200, 'percentage' => 40.0],
                    ],
                ],
            ])),
        ], $history);

        $result = $client->analytics->url('abc123', dimension: 'country');

        $this->assertInstanceOf(UrlAnalyticsResult::class, $result);
        $this->assertNotNull($result->breakdown);
        $this->assertSame('country', $result->breakdown->dimension);
        $this->assertSame(500, $result->breakdown->total);
        $this->assertCount(2, $result->breakdown->data);
        $this->assertSame('US', $result->breakdown->data[0]->value);
        $this->assertSame(300, $result->breakdown->data[0]->clicks);
        $this->assertSame(60.0, $result->breakdown->data[0]->percentage);

        $request = $history[0]['request'];
        $query = $request->getUri()->getQuery();
        $this->assertStringContainsString('dimension=country', $query);
    }

    public function testUrlWithDetail(): void
    {
        $history = [];
        $client = $this->createClient([
            new Response(200, [], json_encode([
                'url' => [
                    'shortCode' => 'abc123',
                    'shortUrl' => 'https://shorter.sh/abc123',
                    'originalUrl' => 'https://example.com',
                ],
                'summary' => [
                    'totalClicks' => 500,
                    'uniqueVisitors' => 400,
                    'prevPeriodClicks' => 300,
                    'prevPeriodUnique' => 250,
                    'topCountry' => 'US',
                    'topReferrer' => 'google.com',
                    'topDevice' => 'Desktop',
                    'topBrowser' => 'Chrome',
                ],
                'timeseries' => [
                    'granularity' => 'day',
                    'data' => [],
                ],
                'breakdowns' => [
                    'country' => [
                        'total' => 500,
                        'data' => [
                            ['value' => 'US', 'clicks' => 300, 'percentage' => 60.0],
                        ],
                    ],
                    'device_type' => [
                        'total' => 500,
                        'data' => [
                            ['value' => 'Desktop', 'clicks' => 400, 'percentage' => 80.0],
                        ],
                    ],
                ],
            ])),
        ], $history);

        $result = $client->analytics->url('abc123', detail: true);

        $this->assertInstanceOf(UrlAnalyticsDetailResult::class, $result);
        $this->assertSame('abc123', $result->url->short_code);
        $this->assertSame('https://example.com', $result->url->original_url);
        $this->assertSame(500, $result->summary->total_clicks);

        // Breakdowns
        $this->assertArrayHasKey('country', $result->breakdowns);
        $this->assertArrayHasKey('device_type', $result->breakdowns);
        $this->assertSame('country', $result->breakdowns['country']->dimension);
        $this->assertSame(500, $result->breakdowns['country']->total);
        $this->assertCount(1, $result->breakdowns['country']->data);
        $this->assertSame('US', $result->breakdowns['country']->data[0]->value);

        $request = $history[0]['request'];
        $query = $request->getUri()->getQuery();
        $this->assertStringContainsString('detail=true', $query);
    }
}
