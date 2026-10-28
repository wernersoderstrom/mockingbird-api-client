<?php

namespace Wernersoderstrom\MockingbirdApiClient\Casts;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;
use Wernersoderstrom\MockingbirdApiClient\Editions\TextData;

final class NonEmptyTextsCast implements Cast
{
    /**
     * @param  array<string, mixed>  $properties
     * @param  CreationContext<Data>  $context
     * @return DataCollection<int, TextData>
     */
    public function cast(
        DataProperty $property,
        mixed $value,
        array $properties,
        CreationContext $context,
    ): DataCollection {
        $texts = collect(is_array($value) ? $value : [])
            ->filter(
                fn (mixed $item): bool => is_array($item)
                    && is_string($item['text'] ?? null)
                    && filled(trim($item['text']))
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
