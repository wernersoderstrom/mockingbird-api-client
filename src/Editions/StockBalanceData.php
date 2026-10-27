<?php

namespace Wernersoderstrom\MockingbirdApiClient\Editions;

use Spatie\LaravelData\Data;

class StockBalanceData extends Data
{
    public function __construct(
        public int $gtin,
        public int $balance,
    ) {}

    public function toApiArray(): array
    {
        return [
            'ean' => $this->gtin,
            'balance' => $this->balance,
        ];
    }
}
