# shorter/sdk

PHP SDK for the [shorter.sh](https://shorter.sh) URL shortener. Requires PHP >=8.1 and Guzzle 7.

## Installation

```bash
composer require shorter/sdk
```

## Quick Start

```php
use Shorter\Sdk\ShorterClient;

$client = new ShorterClient(
    api_key: 'sk_your_key_here',     // or set SHORTER_API_KEY env var
    base_url: 'https://shorter.sh',  // optional, this is the default
);

// Shorten a URL
$result = $client->shorten('https://example.com');
echo $result->short_url;  // https://shorter.sh/xK9mP2

// List your URLs
$list = $client->list(page: 1, limit: 50);
foreach ($list->urls as $url) {
    echo "{$url->short_url} → {$url->original_url} ({$url->click_count} clicks)\n";
}
echo "Total clicks: {$list->total_clicks}\n";

// Delete a URL
$client->delete('xK9mP2');
```

## Authentication

The API key is resolved in order:

1. `api_key` constructor parameter
2. `SHORTER_API_KEY` environment variable

The key must start with `sk_`.

## Analytics

```php
// Overview analytics
$overview = $client->analytics->overview(
    start: '2024-01-01',
    end: '2024-01-31',
);
echo "Total clicks: {$overview->total_clicks}\n";
echo "Unique visitors: {$overview->unique_visitors}\n";

// Top URLs
foreach ($overview->top_urls as $url) {
    echo "{$url->short_url}: {$url->clicks} clicks\n";
}

// Breakdowns (country, device, browser, os, referrer)
foreach ($overview->country_breakdown as $item) {
    echo "{$item->value}: {$item->clicks} ({$item->percentage}%)\n";
}

// Per-URL analytics
$stats = $client->analytics->url('xK9mP2');
echo "Clicks: {$stats->summary->total_clicks}\n";
echo "Top country: {$stats->summary->top_country}\n";

// Per-URL with a specific breakdown dimension
$stats = $client->analytics->url('xK9mP2', dimension: 'country');
echo "Breakdown: {$stats->breakdown->dimension}\n";
foreach ($stats->breakdown->data as $item) {
    echo "  {$item->value}: {$item->clicks}\n";
}

// Detailed analytics (all breakdowns at once)
$detail = $client->analytics->url('xK9mP2', detail: true);
echo "Original URL: {$detail->url->original_url}\n";
foreach ($detail->breakdowns as $dimension => $breakdown) {
    echo "{$dimension}: {$breakdown->total} total\n";
}
```

### Dimensions

Available breakdown dimensions: `country`, `device_type`, `browser`, `os`, `referrer_domain`, `language`

## Error Handling

```php
use Shorter\Sdk\Exceptions\ValidationException;
use Shorter\Sdk\Exceptions\RateLimitException;
use Shorter\Sdk\Exceptions\NetworkException;
use Shorter\Sdk\Exceptions\ShorterException;

try {
    $client->shorten('not-a-url');
} catch (ValidationException $e) {
    echo $e->getMessage();  // "Invalid URL"
    echo $e->errorCode;     // "VALIDATION_ERROR"
    echo $e->status;        // 400
} catch (RateLimitException $e) {
    // Back off and retry
} catch (NetworkException $e) {
    // Connection failed
} catch (ShorterException $e) {
    // Any other API error
}
```

### Exception Classes

| Class | Status | Default Code |
|---|---|---|
| `ValidationException` | 400 | `VALIDATION_ERROR` |
| `AuthenticationException` | 401 | `AUTH_REQUIRED` |
| `ForbiddenException` | 403 | `FORBIDDEN` |
| `NotFoundException` | 404 | `NOT_FOUND` |
| `RateLimitException` | 429 | `RATE_LIMITED` |
| `ServerException` | 500 | `SERVER_ERROR` |
| `NetworkException` | 0 | `NETWORK_ERROR` |

All extend `ShorterException`, which extends `\RuntimeException`.

## Custom HTTP Client

Inject your own Guzzle client for proxies, timeouts, or testing:

```php
use GuzzleHttp\Client;

$client = new ShorterClient(
    api_key: 'sk_...',
    http_client: new Client([
        'timeout' => 10,
        'proxy' => 'http://proxy:8080',
    ]),
);
```

## Requirements

- PHP >= 8.1
- Guzzle >= 7.0

## License

MIT
