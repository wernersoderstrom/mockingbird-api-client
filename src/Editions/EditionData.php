<?php

namespace Wernersoderstrom\MockingbirdApiClient\Editions;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;
use League\ISO3166\ISO3166;
use OutOfBoundsException;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Wernersoderstrom\MockingbirdApiClient\Casts\NonEmptyTextsCast;
use Wernersoderstrom\MockingbirdApiClient\MockingbirdApiClient;

class EditionData extends Data
{
    /** @var array<string, mixed> */
    private array $work = [];

    #[Computed]
    public bool $isMainEdition;

    #[Computed]
    public bool $isWebEdition;

    #[Computed]
    public bool $isTranslated;

    #[Computed]
    public ?string $imprint;

    public function __construct(
        #[MapInputName('productId')]
        public string $id,

        #[MapInputName('editionIdLegacy')]
        public int $legacyId,

        #[MapInputName('workIdLegacy')]
        public int $legacyWorkId,

        #[MapInputName('Title')]
        public string $title,

        public ?string $subtitle,
        public ?string $originalTitle,

        #[MapInputName('ean')]
        public ?int $gtin,

        #[MapInputName('publishingHouse.name')]
        public string $publishingHouse,

        #[MapInputName('brand.name')]
        public ?string $brand,

        public BindingCodeData $bindingCode,

        public bool $isDigital,

        #[DataCollectionOf(ContributorData::class)]
        public DataCollection $contributors,

        #[MapInputName('serie.name')]
        public ?string $serie,

        public ?CostCenterData $costCenter,

        public ?SeasonData $season,

        #[MapInputName('governingCode.name')]
        public string $governingCode,

        #[DataCollectionOf(AwardData::class)]
        public DataCollection $awards,

        public array $keywords,
        public array $bookTypes,

        public ?MainGroupData $mainGroup,

        #[MapInputName('subgroup')]
        public ?SubGroupData $subGroup,

        #[MapInputName('measurements.depth')]
        public ?float $depth,

        #[MapInputName('measurements.height')]
        public ?float $height,

        #[MapInputName('measurements.weight')]
        public ?float $weight,

        #[MapInputName('measurements.width')]
        public ?float $width,

        #[DataCollectionOf(LanguageData::class)]
        public DataCollection $languages,

        #[DataCollectionOf(LanguageData::class)]
        public DataCollection $originalLanguages,

        #[MapInputName('pages')]
        public ?int $pages,

        #[DataCollectionOf(PriceData::class)]
        public DataCollection $prices,

        #[MapInputName('prohibitTextAndDataMining')]
        public bool $textAndDataMiningProhibited,

        #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d\TH:i:s')]
        public ?Carbon $publishingDate,

        public ?int $stockBalance,

        public ?string $technicalProductionTypeName,

        #[MapInputName('vat')]
        public float $vatPercentage,

        #[WithCast(NonEmptyTextsCast::class)]
        #[DataCollectionOf(TextData::class)]
        public DataCollection $texts,

        #[Computed]
        public ?string $internalTitle,

        #[MapInputName('dispositionCode.name')]
        public string $dispositionCode,

        /** TODO
         *
         * assets
         */
    ) {
        $this->internalTitle = $this->resolveInternalTitle();
        $this->isTranslated = $this->resolveIsTranslated();
        $this->pages = $this->pages === 0 ? null : $this->pages;

        $this->work = app(MockingbirdApiClient::class)->get('v1/Work/'.$this->legacyWorkId);
        $this->isMainEdition = $this->id === $this->work['mainEditionProductId'];
        $this->isWebEdition = $this->id === $this->work['webEditionProductId'];

        // Map 0.0 value in measurements to null
        $this->depth = $this->depth === 0.0 ? null : $this->depth;
        $this->height = $this->height === 0.0 ? null : $this->height;
        $this->weight = $this->weight === 0.0 ? null : $this->weight;
        $this->width = $this->width === 0.0 ? null : $this->width;

        $this->imprint = $this->resolveImprint();
    }

    private function resolveInternalTitle(): string
    {
        $bindingCodeMapping = [
            'Podcast' => 'podcast',
            'Hardback' => 'kirja',
            'Saddle-stitched' => 'kirja',
            'Paperback' => 'kirja',
            'Spiral bound' => 'kirja',
            'Flex' => 'kirja',
            'Pocket book' => 'pokkari',
            'Trade paperback or "Jättipokkari"' => 'kirja',
            'Board book' => 'kirja',
            'Downloadable audio file' => 'ä-kirja',
            'CD' => 'cd',
            'MP3-CD' => 'cd',
            'Other audio format' => 'muu audio',
            'Picture-and-audio book' => 'kä-kirja',
            'ePub2' => 'e-kirja',
            'ePub3' => 'e-kirja',
            'Application' => 'sovellus',
            'Kit' => 'paketti',
            'Miscellaneous' => 'muu',
            'Pre-recorded digital audio player' => 'kirjastosoitin',
            'PDF' => 'pdf',
            'Calendar (Hardback)' => 'kalenteri',
            'Calendar (Paperback)' => 'kalenteri',
            'Calendar (Other)' => 'kalenteri',
            'Marketing material' => 'mark. materiaali',
            'Multiple-component retail product' => 'moniosainen',
        ];

        if (array_key_exists($this->bindingCode->name, $bindingCodeMapping) === false) {
            throw new Exception('Could not map binding code for internal title. Binding code: '.$this->bindingCode->name);
        }

        $format = $bindingCodeMapping[$this->bindingCode->name];

        /*
        if (isset($this->product->activePrint->ebookHasAudioFile) && $this->product->activePrint->ebookHasAudioFile === true) {
            $format = 'eä-kirja';
        }*/

        // Space reserved for format + one space
        $spaceForFormat = mb_strlen($format) + 1;

        return trim(mb_substr($this->title, 0, 50 - $spaceForFormat)).' '.$format;
    }

    public function countryOfManufacture(): ?string
    {
        if ($this->isDigital) {
            return null;
        }

        $printer = $this->contributors
            ->toCollection()
            ->where('role.name', 'Printer')
            ->reject(fn (ContributorData $contributor) => Str::contains($contributor->firstName, 'Yhteispainatus') || Str::contains($contributor->lastName ?? '', 'Yhteispainatus'))
            ->first();

        if ($printer === null) {
            return null;
        }

        $contact = app(MockingbirdApiClient::class)->get('v1/Contact/'.$printer->contactIdLegacy);

        foreach ($contact['addresses'] as $address) {
            if (empty($address['country'])) {
                continue;
            }

            try {
                return (new ISO3166)->name($address['country'])['alpha2'];
            } catch (OutOfBoundsException) {
                throw new Exception('Cannot find ISO-3166 code for country named '.$address['country']
                );
            }
        }

        return null;
    }

    public function calculatedPublisherRetailPrice(): ?PriceData
    {
        $resellerPrice = $this->prices
            ->toCollection()
            ->firstWhere('type', 'ResellerPriceIncludingVat')
            ?->value;

        if ($resellerPrice === null) {
            return null;
        }

        if ($resellerPrice === 0.0) {
            $value = 0.0;
        } else {
            $value = $resellerPrice * $this->retailPriceMultiplier();

            // Rounding for pocket books, manga and digital products is up to nearest 10 cents
            if ($this->costCenter?->id === 965 || $this->bindingCode->name === 'Pocket book' || $this->isDigital) {
                $value = ceil($value * 10) / 10;
            } else {
                // All others to nearest 90 cents
                $fraction = $value - floor($value);

                if ($fraction > 0.9) {
                    $value++;
                }

                $value = floor($value) + 0.9;
            }
        }

        return new PriceData(
            currency: 'EUR',
            value: $value,
            type: 'CalculatedPublisherRetailPrice',
            onixCodelistValue: null,
        );
    }

    public function retailPriceMultiplier(): float
    {
        // Manga and pocket books
        if ($this->costCenter?->id === 965 || $this->bindingCode->name === 'Pocket book') {
            return 1.645;
        }

        // Immaterial
        if ($this->isDigital) {
            return 1.435;
        }

        // Other formats and default
        return match ($this->bindingCode->name) {
            'Application',
            'Downloadable audio file',
            'ePub2',
            'ePub3',
            'PDF',
            'Picture-and-audio book',
            'Podcast' => 1.435,

            default => 1.205,
        };
    }

    public function mainEditionCostCenter(): ?CostCenterData
    {
        if (isset($this->work['costCenter']['code']) === false) {
            return null;
        }

        return new CostCenterData(
            id: $this->work['costCenter']['code'],
            name: $this->work['costCenter']['name'],
        );
    }

    public function mainEditionGtin(): int
    {
        return $this->work['mainEditionIsbn'];
    }

    public function resolveIsTranslated(): bool
    {
        $translators = $this->contributors
            ->toCollection()
            ->filter(
                fn (ContributorData $contributor) => Str::contains($contributor->role->name, 'Translator')
            );

        return $translators->isNotEmpty();
    }

    private function resolveImprint(): ?string
    {
        if ($this->brand === null) {
            return null;
        }

        if ($this->brand === 'Johnny Kniga') {
            return null;
        }

        if ($this->brand === $this->publishingHouse) {
            return null;
        }

        return $this->brand;
    }
}
