<?php

namespace Wernersoderstrom\MockingbirdApiClient\Editions;

use Spatie\LaravelData\Data;
use Wernersoderstrom\MockingbirdApiClient\Exceptions\UnsupportedBindingCode;

class BindingCodeData extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public string $onixCodelistValue,
    ) {}

    public static function fromApi(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            onixCodelistValue: match ($data['id']) {
                'BB' => 'BB',
                'BF' => 'BF',
                'BC' => 'BC',
                'BE' => 'BE',
                'BCB116' => 'BC',
                'BCB104' => 'BC',
                // 'BCB106' =>	'
                'BH' => 'BH',
                'AJA103' => 'AJ',
                'ACA101' => 'AC',
                'AEA103' => 'AE',
                'EDW994' => 'ED',
                'EPUB2' => 'ED',
                'EPUB3' => 'ED',
                'PDF' => 'EA',
                'ED' => 'ED',
                'RAGB' => 'AJ',
                'CALHB' => 'PC',
                'CALOTH' => 'PC',
                'CALPB' => 'PC',
                'MARK' => 'ZZ',
                'MISC' => 'ZZ',
                'MULTI' => 'SA',
                default => throw UnsupportedBindingCode::forCode($data['id']),
            },
        );
    }
}
