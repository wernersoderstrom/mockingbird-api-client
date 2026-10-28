<?php

namespace Wernersoderstrom\MockingbirdApiClient\Facades;

use Illuminate\Support\Facades\Facade;
use Wernersoderstrom\MockingbirdApiClient\Editions\EditionService;

/**
 * @method static \Wernersoderstrom\MockingbirdApiClient\Editions\EditionData get(string $id)
 * @method static \Wernersoderstrom\MockingbirdApiClient\Editions\EditionData getByGtin(string|int $gtin)
 * @method static string|null countryOfManufacture(\Wernersoderstrom\MockingbirdApiClient\Editions\EditionData $edition)
 * @method static void updateStockBalances(array<\Wernersoderstrom\MockingbirdApiClient\Editions\StockBalanceData> $stockBalances)
 *
 * @see EditionService
 */
class Edition extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return EditionService::class;
    }
}
