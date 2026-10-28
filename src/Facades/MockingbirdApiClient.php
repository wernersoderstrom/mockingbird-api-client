<?php

namespace Wernersoderstrom\MockingbirdApiClient\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array<string, mixed> get(string $path, array<string, mixed> $query = [])
 * @method static array<mixed>|null put(string $path, array<string, mixed>|list<mixed> $payload)
 *
 * @see \Wernersoderstrom\MockingbirdApiClient\MockingbirdApiClient
 */
class MockingbirdApiClient extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Wernersoderstrom\MockingbirdApiClient\MockingbirdApiClient::class;
    }
}
