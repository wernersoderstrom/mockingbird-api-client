<?php

namespace Wernersoderstrom\MockingbirdApiClient\Editions;

use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;

class SeasonData extends Data
{
    /** `2026/1` for spring, `2026/2` for autumn, `null` without a year */
    #[Computed]
    public ?string $name;

    /**
     * @param  int|null  $year  `null` when the period is `N/A`
     * @param  string|null  $period  `Spring`, `Autumn` or a number. `null` when `N/A`
     */
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
