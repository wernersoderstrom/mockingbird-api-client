<?php

namespace Wernersoderstrom\MockingbirdApiClient\Editions;

use Spatie\LaravelData\Data;

class StockBalanceData extends Data
{
    /**
     * @param  int  $gtin  EAN-13 / ISBN-13 of the edition
     * @param  int  $balance  New stock balance
     */
    public function __construct(
        public int $gtin,
        public int $balance,
    ) {}
}
