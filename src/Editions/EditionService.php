<?php

namespace Wernersoderstrom\MockingbirdApiClient\Editions;

use Wernersoderstrom\MockingbirdApiClient\MockingbirdApiClient;

class EditionService
{
    public function __construct(
        protected MockingbirdApiClient $client,
    ) {}

    public function get(string $id): EditionData
    {
        $response = $this->client->get(
            "/v1/Edition/{$id}"
        );

        return EditionData::from($response);
    }

    public function getByGtin(string|int $gtin): EditionData
    {
        return EditionData::from(
            $this->client->get(
                "v1/EditionByIsbn/{$gtin}"
            )
        );
    }

    /**
     * @param  array<StockBalanceData>  $stockBalances
     */
    public function updateStockBalances(array $stockBalances): void
    {
        $payload = collect($stockBalances)
            ->map(fn (StockBalanceData $stockBalance) => [
                'ean' => strval($stockBalance->gtin),
                'balance' => $stockBalance->balance,
            ])
            ->values()
            ->all();

        $this->client->put(
            'v1/Edition/stock',
            $payload
        );
    }
}
