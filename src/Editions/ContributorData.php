<?php

namespace Wernersoderstrom\MockingbirdApiClient\Editions;

use Spatie\LaravelData\Data;

class ContributorData extends Data
{
    public function __construct(
        public int $contactIdLegacy,
        public string $contactId,
        public string $firstName,
        public ?string $lastName,
        public ContributorRoleData $role,
    ) {}

    public function __get(string $name): mixed
    {
        return match ($name) {
            'fullName' => trim(
                $this->firstName.' '.($this->lastName ?? '')
            ),
            default => null,
        };
    }
}
