<?php

namespace Wernersoderstrom\MockingbirdApiClient\Editions;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class LanguageData extends Data
{
    /**
     * @param  string  $iso639LanguageCode  ISO 639-2/B code, e.g. `fin`
     */
    public function __construct(
        #[MapInputName('code')]
        public string $iso639LanguageCode,
        public string $name,
    ) {}
}
