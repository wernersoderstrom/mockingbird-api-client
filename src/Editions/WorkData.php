<?php

namespace Wernersoderstrom\MockingbirdApiClient\Editions;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class WorkData extends Data
{
    public function __construct(
        public ?string $mainEditionProductId,
        public ?string $webEditionProductId,
        #[MapInputName('mainEditionIsbn')]
        public ?int $mainEditionGtin,
        public ?CostCenterData $costCenter,
    ) {}
}
