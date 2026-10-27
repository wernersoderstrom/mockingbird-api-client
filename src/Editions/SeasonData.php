<?php

namespace Wernersoderstrom\MockingbirdApiClient\Editions;

use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;

class SeasonData extends Data
{
    #[Computed]
    public ?string $name;

    public function __construct(
        public ?int $year = null,
        public ?string $period = null,
    ) {
        if ($this->period === 'N/A') {
            $this->period = null;
            $this->year = null;
        }

        $this->name = match (true) {
            $this->year === null => null,
            $this->period === null => (string) $this->year,
            $this->period === 'Spring' => "{$this->year}/1",
            $this->period === 'Autumn' => "{$this->year}/2",
            default => "{$this->year}/{$this->period}",
        };
    }
}
