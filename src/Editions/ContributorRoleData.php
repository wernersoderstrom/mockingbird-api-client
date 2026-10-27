<?php

namespace Wernersoderstrom\MockingbirdApiClient\Editions;

use Spatie\LaravelData\Data;

class ContributorRoleData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $onixCodelistValue,
    ) {}

    public static function fromApi(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            onixCodelistValue: match ($data['name']) {
                'Adapted by' => 'B05',
                'Adapted by in-house' => 'B05',
                'Afterword' => 'A19',
                'AI Reader – female' => 'E07',
                'AI Reader – male' => 'E07',
                'AI Reader – unspecified' => 'E07',
                'AI Reader – voice replica' => 'E07',
                'Arranged by' => 'B25',
                'Assistant' => 'Z01',
                'Assisted by' => 'Z01',
                'Author' => 'A01',
                'Author in-house' => 'A01',
                'Composer' => 'A06',
                'Cover design or artwork by' => 'A36',
                'Designer, cover' => 'A36',
                'Designer, cover in-house' => 'A36',
                'Designer, entire book' => 'A11',
                'Designer, entire book in-house' => 'A11',
                'Editing author' => 'B01',
                'Editing author in-house' => 'B01',
                'Editor in Chief' => 'B11',
                'Epilogue' => 'A22',
                'Foreword' => 'A23',
                'Graphic Designer' => 'A11',
                'Illustrator' => 'A12',
                'Illustrator, cover' => 'A36',
                'Illustrator, cover in-house' => 'A36',
                'Illustrator, entire book' => 'A12',
                'Illustrator, entire book in-house' => 'A12',
                'Index' => 'A34',
                'Introduction' => 'A24',
                'Introduction read by' => 'E11',
                'Maps' => 'A39',
                'Performed by' => 'E08',
                'Photographer' => 'A13',
                'Photographer, cover' => 'A36',
                'Photographer, entire book' => 'A13',
                'Preface' => 'A15',
                'Prologue' => 'A16',
                'Reader' => 'E07',
                'Singer' => 'E05',
                'Translator' => 'B06',
                'Translator in-house' => 'B06',
                'With' => 'A02',

                default => null,
            },
        );
    }
}
