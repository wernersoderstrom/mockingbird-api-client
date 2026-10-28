<?php

namespace Wernersoderstrom\MockingbirdApiClient\Editions;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Hidden;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Wernersoderstrom\MockingbirdApiClient\Casts\NonEmptyTextsCast;
use Wernersoderstrom\MockingbirdApiClient\Exceptions\UnsupportedBindingCode;

class EditionData extends Data
{
    /** This edition is the work's main edition */
    #[Computed]
    public bool $isMainEdition;

    /** This edition is the work's web edition */
    #[Computed]
    public bool $isWebEdition;

    /** Has a contributor whose role contains "Translator" */
    #[Computed]
    public bool $isTranslated;

    /** The brand, unless it is Johnny Kniga or the same as the publishing house */
    #[Computed]
    public ?string $imprint;

    /**
     * @param  string  $id  Mockingbird product ID
     * @param  int|null  $gtin  EAN-13 / ISBN-13
     * @param  DataCollection<int, ContributorData>  $contributors
     * @param  DataCollection<int, AwardData>  $awards
     * @param  array<int, string>  $keywords
     * @param  array<int, string>  $bookTypes
     * @param  float|null  $depth  `null` when 0 in Mockingbird
     * @param  float|null  $height  `null` when 0 in Mockingbird
     * @param  float|null  $weight  `null` when 0 in Mockingbird
     * @param  float|null  $width  `null` when 0 in Mockingbird
     * @param  DataCollection<int, LanguageData>  $languages
     * @param  DataCollection<int, LanguageData>  $originalLanguages
     * @param  int|null  $pages  `null` when 0 in Mockingbird
     * @param  DataCollection<int, PriceData>  $prices
     * @param  float  $vatRate  VAT as a fraction, e.g. 0.135 for 13.5 %
     * @param  DataCollection<int, TextData>  $texts  Marketing texts as HTML, empty texts removed
     * @param  string|null  $internalTitle  Title shortened so that it and the format suffix (e.g. `kirja`, `e-kirja`) fit in 50 characters
     */
    public function __construct(
        #[MapInputName('productId')]
        public string $id,

        #[MapInputName('editionIdLegacy')]
        public int $legacyId,

        #[MapInputName('workIdLegacy')]
        public int $legacyWorkId,

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
        public float $vatRate,

        #[WithCast(NonEmptyTextsCast::class)]
        #[DataCollectionOf(TextData::class)]
        public DataCollection $texts,

        #[Computed]
        public ?string $internalTitle,

        #[MapInputName('dispositionCode.name')]
        public string $dispositionCode,

        #[Hidden]
        public WorkData $work,

        /** TODO
         *
         * assets
         */
    ) {
        $this->internalTitle = $this->resolveInternalTitle();
        $this->isTranslated = $this->resolveIsTranslated();
        $this->pages = $this->pages === 0 ? null : $this->pages;

        $this->isMainEdition = $this->id === $this->work->mainEditionProductId;
        $this->isWebEdition = $this->id === $this->work->webEditionProductId;

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
            throw UnsupportedBindingCode::forInternalTitle($this->bindingCode->name);
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

    /**
     * The printer contributor, excluding "Yhteispainatus". `null` for digital products.
     */
    public function printer(): ?ContributorData
    {
        if ($this->isDigital) {
            return null;
        }

        return $this->contributors
            ->toCollection()
            ->where('role.name', 'Printer')
            ->reject(fn (ContributorData $contributor) => Str::contains($contributor->fullName, 'Yhteispainatus'))
            ->first();
    }

    /**
     * Retail price calculated from the reseller price including VAT.
     *
     * Pocket books, manga and digital products are rounded up to the next 10 cents, everything else to the next .90.
     */
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
            // Integer arithmetic in units of 1/100000 EUR to avoid float rounding errors
            $units = (int) round($resellerPrice * 100) * (int) round($this->retailPriceMultiplier() * 1000);

            // Rounding for pocket books, manga and digital products is up to nearest 10 cents
            if ($this->costCenter?->id === 965 || $this->bindingCode->name === 'Pocket book' || $this->isDigital) {
                $value = intdiv($units + 9999, 10000) / 10;
            } else {
                // All others to nearest 90 cents
                $euros = intdiv($units, 100000);

                if ($units % 100000 > 90000) {
                    $euros++;
                }

                $value = $euros + 0.9;
            }
        }

        return new PriceData(
            currency: 'EUR',
            value: $value,
            type: 'CalculatedPublisherRetailPrice',
            onixCodelistValue: null,
        );
    }

    /**
     * Multiplier from the reseller price including VAT to the retail price.
     */
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

    /**
     * Cost center of the work.
     */
    public function mainEditionCostCenter(): ?CostCenterData
    {
        return $this->work->costCenter;
    }

    /**
     * GTIN of the work's main edition.
     */
    public function mainEditionGtin(): ?int
    {
        return $this->work->mainEditionGtin;
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
