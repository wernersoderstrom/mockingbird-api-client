<?php

namespace Wernersoderstrom\MockingbirdApiClient\Editions;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class SubGroupData extends Data
{
    public function __construct(
        #[MapInputName('erpId')]
        public int $id,
        public string $name,
    ) {}
}
