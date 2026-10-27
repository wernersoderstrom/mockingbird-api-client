<?php

namespace Wernersoderstrom\MockingbirdApiClient\Casts;

use Wernersoderstrom\MockingbirdApiClient\Editions\TextData;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

final class NonEmptyTextsCast implements Cast
{
    public function cast(
        DataProperty $property,
        mixed $value,
        array $properties,
        CreationContext $context,
    ): DataCollection {
        $texts = collect(is_array($value) ? $value : [])
            ->filter(
                fn (mixed $item): bool => is_array($item)
                    && filled(trim((string) ($item['text'] ?? '')))
            )
            ->map(
                fn (array $item): TextData => TextData::from($item)
            )
            ->values()
            ->all();

        return new DataCollection(
            TextData::class,
            $texts,
        );
    }
}
