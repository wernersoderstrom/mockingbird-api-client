<?php

namespace Wernersoderstrom\MockingbirdApiClient\Editions;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class CostCenterData extends Data
{
    public function __construct(
        #[MapInputName('code')]
        public int $id,
        public string $name,
    ) {}
}
