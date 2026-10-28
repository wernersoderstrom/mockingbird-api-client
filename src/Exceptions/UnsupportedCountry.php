<?php

namespace Wernersoderstrom\MockingbirdApiClient\Exceptions;

use RuntimeException;

class UnsupportedCountry extends RuntimeException
{
    public static function forName(string $name): self
    {
        return new self(
            sprintf(
                'Cannot find ISO-3166 code for country named "%s".',
                $name
            )
        );
    }
}
