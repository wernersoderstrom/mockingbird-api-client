<?php

namespace Wernersoderstrom\MockingbirdApiClient\Editions;

use Spatie\LaravelData\Data;

class AwardData extends Data
{
    public function __construct(
        public string $name,
    ) {}
}
