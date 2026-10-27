<?php

namespace Wernersoderstrom\MockingbirdApiClient\Editions;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class LanguageData extends Data
{
    public function __construct(
        #[MapInputName('code')]
        public string $iso639LanguageCode,
        public string $name,
    ) {}
}
