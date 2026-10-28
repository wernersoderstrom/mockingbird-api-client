<?php

namespace Wernersoderstrom\MockingbirdApiClient\Exceptions;

use RuntimeException;

class UnsupportedBindingCode extends RuntimeException
{
    public static function forCode(string $code): self
    {
        return new self(
            sprintf(
                'Unsupported binding code "%s".',
                $code
            )
        );
    }

    public static function forInternalTitle(string $name): self
    {
        return new self(sprintf('Could not map binding code "%s" to an internal title format.', $name));
    }
}
