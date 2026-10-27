<?php

namespace Wernersoderstrom\MockingbirdApiClient\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Wernersoderstrom\MockingbirdApiClient\MockingbirdApiClient
 */
class MockingbirdApiClient extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Wernersoderstrom\MockingbirdApiClient\MockingbirdApiClient::class;
    }
}
