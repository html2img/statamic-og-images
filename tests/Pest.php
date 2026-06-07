<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Html2img\Html2imgClient;
use Html2img\StatamicOgImages\Rendering\OgImageGenerator;
use Html2img\StatamicOgImages\Tests\TestCase;
use Psr\Http\Message\RequestInterface;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Support\Str;

uses(TestCase::class)->in(__DIR__);

/**
 * Bind a mocked Guzzle client as `og-images.http` so the SDK makes no real
 * call, and rebuild the bindings that depend on it.
 *
 * @param  list<Response|Throwable>  $queue
 * @param  array<int, mixed>  $history
 */
function fakeHttp(array $queue, array &$history = []): void
{
    $mock = new MockHandler($queue);
    $stack = HandlerStack::create($mock);
    $stack->push(Middleware::history($history));

    $guzzle = new Client([
        'handler' => $stack,
        'base_uri' => Html2imgClient::DEFAULT_BASE_URI,
    ]);

    app()->instance('og-images.http', $guzzle);

    foreach ([Html2imgClient::class, OgImageGenerator::class] as $abstract) {
        app()->forgetInstance($abstract);
    }
}

/**
 * @param  array<string, mixed>  $body
 */
function jsonResponse(int $status, array $body): Response
{
    return new Response($status, ['Content-Type' => 'application/json'], json_encode($body, JSON_THROW_ON_ERROR));
}

/**
 * @param  array<int, mixed>  $history
 * @return array<string, mixed>
 */
function lastRequestBody(array $history): array
{
    $last = end($history);
    $request = is_array($last) ? ($last['request'] ?? null) : null;

    if (! $request instanceof RequestInterface) {
        return [];
    }

    $decoded = json_decode((string) $request->getBody(), true);

    return is_array($decoded) ? $decoded : [];
}

function makeCollection(string $handle): void
{
    Collection::make($handle)->title(ucfirst($handle))->save();
}

/**
 * @param  array<string, mixed>  $data
 */
function makeEntry(string $collection, array $data, ?string $id = null): Statamic\Contracts\Entries\Entry
{
    $entry = Entry::make()
        ->collection($collection)
        ->slug($data['slug'] ?? Str::slug($data['title'] ?? 'entry'))
        ->data($data);

    if ($id) {
        $entry->id($id);
    }

    $entry->save();

    return $entry;
}

/**
 * Enable the addon for the given collections via config (the cascade falls
 * back to config when no Control Panel value is stored).
 *
 * @param  list<string>  $collections
 */
function enableCollections(array $collections): void
{
    config()->set('statamic-og-images.collections', $collections);
}
