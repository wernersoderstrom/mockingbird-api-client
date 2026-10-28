<?php

namespace Wernersoderstrom\MockingbirdApiClient\Editions;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class PriceData extends Data
{
    /**
     * @param  string  $currency  From `price.currency`
     * @param  float  $value  From `price.value`
     * @param  string|null  $onixCodelistValue  ONIX price type code (List 58), mapped from `priceType`. `null` when there is no mapping
     */
    public function __construct(
        public string $currency,
        public float $value,
        #[MapInputName('priceType')]
        public string $type,
        public ?string $onixCodelistValue,
    ) {}

    /**
     * @param  array{price: array{currency: string, value: float|int}, priceType: string}  $data
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
