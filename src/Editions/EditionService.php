<?php

namespace Wernersoderstrom\MockingbirdApiClient\Editions;

use Illuminate\Support\Arr;
use League\ISO3166\ISO3166;
use OutOfBoundsException;
use Wernersoderstrom\MockingbirdApiClient\Exceptions\UnsupportedCountry;
use Wernersoderstrom\MockingbirdApiClient\MockingbirdApiClient;

class EditionService
{
    public function __construct(
        protected MockingbirdApiClient $client,
    ) {}

    public function get(string $id): EditionData
    {
        return $this->fromResponse(
            $this->client->get("v1/Edition/{$id}")
        );
    }

    public function getByGtin(string|int $gtin): EditionData
    {
        return $this->fromResponse(
            $this->client->get("v1/EditionByIsbn/{$gtin}")
        );
    }

    public function countryOfManufacture(EditionData $edition): ?string
    {
        $printer = $edition->printer();

        if ($printer === null) {
            return null;
        }

        /** @var array{addresses?: list<array{country?: string|null}>|null} $contact */
        $contact = $this->client->get('v1/Contact/'.$printer->contactIdLegacy);

        foreach ($contact['addresses'] ?? [] as $address) {
            if (empty($address['country'])) {
                continue;
            }

            try {
                return (new ISO3166)->name($address['country'])['alpha2'];
            } catch (OutOfBoundsException) {
                throw UnsupportedCountry::forName($address['country']);
            }
        }

        return null;
    }

    /**
     * @param  array<StockBalanceData>  $stockBalances
     */
    public function updateStockBalances(array $stockBalances): void
    {
        /** @var list<array{ean: string, balance: int}> $payload */
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

    /**
     * @param  array<string, mixed>  $edition
     */
    protected function fromResponse(array $edition): EditionData
    {
        return EditionData::from([
            ...$edition,
            'work' => $this->client->get('v1/Work/'.Arr::integer($edition, 'workIdLegacy')),
        ]);
    }
}
