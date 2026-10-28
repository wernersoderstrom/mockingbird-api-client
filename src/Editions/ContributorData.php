<?php

namespace Wernersoderstrom\MockingbirdApiClient\Editions;

use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;

class ContributorData extends Data
{
    #[Computed]
    public string $fullName;

    public function __construct(
        public int $contactIdLegacy,
        public string $contactId,
        public string $firstName,
        public ?string $lastName,
        public ContributorRoleData $role,
    ) {
        $this->fullName = trim($this->firstName.' '.($this->lastName ?? ''));
    }
}
