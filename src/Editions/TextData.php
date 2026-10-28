<?php

namespace Wernersoderstrom\MockingbirdApiClient\Editions;

use Spatie\LaravelData\Data;

class TextData extends Data
{
    /**
     * @param  string  $text  HTML
     */
    public function __construct(
        public string $text,
    ) {}
}
