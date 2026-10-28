<?php

namespace Wernersoderstrom\MockingbirdApiClient\Editions;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class PriceData extends Data
{
    public function __construct(
        public string $currency,
        public float $value,
        #[MapInputName('priceType')]
        public string $type,
        public ?string $onixCodelistValue,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromApi(array $data): self
    {
        return new self(
            currency: $data['price']['currency'],
            value: $data['price']['value'],
            type: $data['priceType'],
            onixCodelistValue: match ($data['priceType']) {
                'PublisherRetailPrice' => '41',
                'PublisherRetailPriceIncludingVat' => '42',
                'ResellerPrice' => '05',
                'ResellerPriceIncludingVat' => '07',
                default => null,
            },
        );
    }
}
