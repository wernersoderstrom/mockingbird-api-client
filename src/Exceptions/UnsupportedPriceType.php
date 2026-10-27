<?php

namespace Wernersoderstrom\MockingbirdApiClient\Exceptions;

use RuntimeException;

class UnsupportedPriceType extends RuntimeException
{
    public static function forCode(string $priceType): self
    {
        return new self(
            sprintf(
                'Unsupported pricetype "%s".',
                $priceType
            )
        );
    }
}
