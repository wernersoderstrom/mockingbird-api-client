<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelData\DataCollection;
use Wernersoderstrom\MockingbirdApiClient\Editions\AwardData;
use Wernersoderstrom\MockingbirdApiClient\Editions\ContributorData;
use Wernersoderstrom\MockingbirdApiClient\Editions\ContributorRoleData;
use Wernersoderstrom\MockingbirdApiClient\Editions\EditionService;
use Wernersoderstrom\MockingbirdApiClient\Editions\LanguageData;
use Wernersoderstrom\MockingbirdApiClient\Editions\PriceData;
use Wernersoderstrom\MockingbirdApiClient\Editions\StockBalanceData;
use Wernersoderstrom\MockingbirdApiClient\Editions\TextData;
use Wernersoderstrom\MockingbirdApiClient\MockingbirdApiClient;

it('can authenticate and fetch token from the API', function () {
    $client = app(MockingbirdApiClient::class);
    $reflection = new ReflectionClass($client);
    $method = $reflection->getMethod('accessToken');

    $token = $method->invoke($client);

    expect($token)
        ->toBeString()
        ->not->toBeEmpty();
})->group('integration');

it('can fetch edition data correctly', function () {
    $edition = app(EditionService::class)->get('1ca73850-96c2-4ac3-8b98-44d35c9378d1');

    expect($edition->id)->toBe('1ca73850-96c2-4ac3-8b98-44d35c9378d1');
    expect($edition->legacyId)->toBeInt()->toBe(244940);
    expect($edition->legacyWorkId)->toBeInt()->toBe(244939);
    expect($edition->title)->toBe('Murtuneet mielet');
    expect($edition->gtin)->toBeInt()->toBe(9789510374665);
    expect($edition->bindingCode->id)->toBe('BB');
    expect($edition->bindingCode->name)->toBe('Hardback');
    expect($edition->bindingCode->onixCodelistValue)->toBe('BB');
    expect($edition->brand)->toBe('WSOY');
    expect($edition->costCenter->id)->toBeInt()->toBe(313);
    expect($edition->costCenter->name)->toBe('WSOY - Tietokirjat');
    expect($edition->governingCode)->toBe('Sold out');
    expect($edition->keywords)->toContain('traumat', 'mielenterveys');

    expect($edition->awards)->toBeInstanceOf(DataCollection::class)
        ->and($edition->awards)->toHaveCount(1)
        ->and($edition->awards->first())->toBeInstanceOf(AwardData::class)
        ->and($edition->awards->first()->name)->toBe('Tieto-Finlandia-palkinto');

    expect($edition->contributors)->toBeInstanceOf(DataCollection::class)
        ->and($edition->contributors)->toHaveCount(5)
        ->and($edition->contributors->first())->toBeInstanceOf(ContributorData::class)
        ->and($edition->contributors->first()->firstName)->toBe('Anssi')
        ->and($edition->contributors->first()->lastName)->toBe('Mäkinen')
        ->and($edition->contributors->first()->fullName)->toBe('Anssi Mäkinen')
        ->and($edition->contributors->first()->role)->toBeInstanceOf(ContributorRoleData::class)
        ->and($edition->contributors->first()->role->name)->toBe('Project Manager')
        ->and($edition->contributors->first()->role->id)->toBeInt()->toBe(326);

    expect($edition->languages)->toBeInstanceOf(DataCollection::class)
        ->and($edition->languages)->toHaveCount(1)
        ->and($edition->languages->first())->toBeInstanceOf(LanguageData::class)
        ->and($edition->languages->first()->iso639LanguageCode)->toBe('fin')
        ->and($edition->languages->first()->name)->toBe('Finnish');

    expect($edition->mainGroup->name)->toBe('Tietokirjallisuus');
    expect($edition->mainGroup->id)->toBeInt()->toBe(7);
    expect($edition->subGroup->name)->toBe('Historia');
    expect($edition->subGroup->id)->toBeInt()->toBe(4);
    expect($edition->depth)->toBeFloat()->toBe(37.0000);
    expect($edition->height)->toBeFloat()->toBe(218.0000);
    expect($edition->weight)->toBeFloat()->toBe(0.6150);
    expect($edition->width)->toBeFloat()->toBe(140.000);
    expect($edition->originalLanguages)->toBeEmpty();
    expect($edition->pages)->toBeInt()->toBe(475);
    expect($edition->textAndDataMiningProhibited)->toBeFalse();
    expect($edition->publishingDate->format('Y-m-d'))->toBe('2013-08-15');
    expect($edition->publishingHouse)->toBe('WSOY');

    expect($edition->season->period)->toBe('Autumn');
    expect($edition->season->year)->toBeInt()->toBe(2013);
    expect($edition->season->name)->toBe('2013/2');

    expect($edition->stockBalance)->toBe(0);
    expect($edition->technicalProductionTypeName)->toBe('BBF Generic');

    expect($edition->vatPercentage)->toBeFloat()->toBe(0.1350);

    expect($edition->texts)->toBeInstanceOf(DataCollection::class)
        ->and($edition->texts)->toHaveCount(4)
        ->and($edition->texts->first())->toBeInstanceOf(TextData::class)
        ->and($edition->texts->first()->text)->toContain('Aseiden vaiettua moni veteraani heräsi öisiin painajaisiinsa.');

})->group('integration');

it('can fetch prices correctly', function () {
    $edition = app(EditionService::class)->get('1ca73850-96c2-4ac3-8b98-44d35c9378d1');

    expect($edition->prices)->toBeInstanceOf(DataCollection::class)
        ->and($edition->prices)->toHaveCount(4)
        ->and($edition->prices->first())->toBeInstanceOf(PriceData::class)
        ->and($edition->prices->first()->value)->toBeFloat()->toBe(41.32)
        ->and($edition->prices->first()->currency)->toBe('EUR')
        ->and($edition->prices->first()->type)->toBe('PublisherRetailPrice')
        ->and($edition->prices->first()->onixCodelistValue)->toBe('41');
})->group('integration');

it('can fetch calculated publisher retail prices correctly', function () {
    // Hardback
    $edition = app(EditionService::class)->get('81621fd3-648f-412a-b08f-ab325c3bdf3e');
    $price = $edition->calculatedPublisherRetailPrice();

    expect($price)->not->toBeNull();
    expect($price->type)->toBe('CalculatedPublisherRetailPrice');
    expect($price->currency)->toBe('EUR');
    expect($price->onixCodelistValue)->toBeNull();
    expect($price->value)->toBeFloat()->toBe(22.9);

    // Pocket book
    $edition = app(EditionService::class)->get('e56c79de-ebbd-4f71-a0f1-d92304b797f2');
    expect($edition->calculatedPublisherRetailPrice()->value)->toBeFloat()->toBe(10.4);

    // E-book
    $edition = app(EditionService::class)->get('d8a1c1ae-dfe5-476f-88e4-7a3a157dc7d0');
    expect($edition->calculatedPublisherRetailPrice()->value)->toBeFloat()->toBe(7.6);

    // Product without any price
    $edition = app(EditionService::class)->get('4f9414aa-af6a-4c2e-b655-ae6ea32c007f');
    expect($edition->calculatedPublisherRetailPrice()->value)->toBeFloat()->toBe(0.0);

    // CD
    $edition = app(EditionService::class)->get('132692a2-f2bd-43bd-b3cd-61cc6be77485');
    expect($edition->calculatedPublisherRetailPrice()->value)->toBeFloat()->toBe(19.9);
})->group('integration');

it('can handle missing season period correctly', function () {
    $edition = app(EditionService::class)->get('a4451011-aa86-471d-8964-31fc175ff1ee');

    expect($edition->id)->toBe('a4451011-aa86-471d-8964-31fc175ff1ee');
    expect($edition->season->period)->toBeNull();
    expect($edition->season->year)->toBeInt()->toBe(2024);
})->group('integration');

it('can generate internal title correctly', function (string $editionId, string $expectedInternalTitle) {
    $edition = app(EditionService::class)->get($editionId);
    expect($edition->internalTitle)->toBe($expectedInternalTitle);
})->with('internal titles')->group('integration');

it('can generate internal title for ebooks with audio correctly', function (string $editionId, string $expectedInternalTitle) {
    $edition = app(EditionService::class)->get($editionId);
    expect($edition->internalTitle)->toBe($expectedInternalTitle);
})->with('internal titles for ebooks with audio')
    ->group('integration')
    ->skip('Waiting for ebookHasAudioFile in the Mockingbird API response');

it('can update stock balances correctly', function () {
    Http::fake([
        '*/v1/Edition/stock' => Http::response(),
    ]);

    app(EditionService::class)->updateStockBalances([
        new StockBalanceData(gtin: 9789510368633, balance: 5),
        new StockBalanceData(gtin: 9789524161558, balance: 7),
    ]);

    Http::assertSent(fn (Request $request) => $request->method() === 'PUT'
        && str_ends_with($request->url(), 'v1/Edition/stock')
        && $request->data() === [
            ['ean' => '9789510368633', 'balance' => 5],
            ['ean' => '9789524161558', 'balance' => 7],
        ]
    );
})->group('integration');

it('can fetch edition with gtin correctly', function () {
    $editionService = app(EditionService::class);

    $editionWithId = $editionService->get('1ca73850-96c2-4ac3-8b98-44d35c9378d1');
    $editionWithGtin = $editionService->getByGtin(9789510374665);

    // The output should be identical
    expect($editionWithGtin->id)->toBe('1ca73850-96c2-4ac3-8b98-44d35c9378d1');
    expect($editionWithGtin->gtin)->toBeInt()->toBe(9789510374665);
    expect($editionWithId->toArray())->toEqual($editionWithGtin->toArray());

})->group('integration');

it('can fetch subtitle correctly', function () {
    $editionService = app(EditionService::class);
    $edition = $editionService->get('ffeea60f-3cdc-479c-8486-620b60695311');
    expect($edition->subtitle)->toBe('Historiaa ja reseptiikkaa');
})->group('integration');

it('can fetch original title correctly', function () {
    $edition = app(EditionService::class)->get('02906b41-c217-4e68-98a0-fa5212ea47b2');
    expect($edition->originalTitle)->toBe('Århundradets kärlekssaga');
})->group('integration');

it('can fetch series correctly', function () {
    $edition = app(EditionService::class)->get('1ca73850-96c2-4ac3-8b98-44d35c9378d1');
    expect($edition->serie)->toBeNull();

    $edition = app(EditionService::class)->get('e207df26-18f7-463b-b0d3-c661fd67eda3');
    expect($edition->serie)->toBe('Mielensäpahoittaja');

})->group('integration');

it('can fetch book types correctly', function () {
    $edition = app(EditionService::class)->get('84113283-4fc8-4f77-9c3f-0f1e71710575');
    expect($edition->bookTypes)->toContain('Booktok', 'Tiktok', 'spicy');
})->group('integration');

it('can get main editions cost center correctly', function () {
    $edition = app(EditionService::class)->get('d8a1c1ae-dfe5-476f-88e4-7a3a157dc7d0');
    expect($edition->mainEditionCostCenter()->id)->toBe(301);
})->group('integration');

it('can get main editions gtin correctly', function () {
    $edition = app(EditionService::class)->get('81621fd3-648f-412a-b08f-ab325c3bdf3e');
    expect($edition->mainEditionGtin())->toBe(9789510366264);

    // ePub version of the same book
    $edition = app(EditionService::class)->get('d8a1c1ae-dfe5-476f-88e4-7a3a157dc7d0');
    expect($edition->mainEditionGtin())->toBe(9789510366264);
})->group('integration');

it('can check if edition is main edition correctly', function () {
    $edition = app(EditionService::class)->get('132692a2-f2bd-43bd-b3cd-61cc6be77485');
    expect($edition->isMainEdition)->toBeTrue();

    // Product that is not main edition
    $edition = app(EditionService::class)->get('26aef4ea-8ae0-4ec7-b2e8-d62f6f82e570');
    expect($edition->isMainEdition)->toBeFalse();
})->group('integration');

it('can check if edition is web edition correctly', function () {
    $edition = app(EditionService::class)->get('e207df26-18f7-463b-b0d3-c661fd67eda3');
    expect($edition->isWebEdition)->toBeTrue();

    // Product that is not web edition
    $edition = app(EditionService::class)->get('416fb675-0cae-466b-ac63-7da5f014eb32');
    expect($edition->isWebEdition)->toBeFalse();
})->group('integration');

it('can get country of manufacture correctly', function () {
    $edition = app(EditionService::class)->get('81621fd3-648f-412a-b08f-ab325c3bdf3e');
    expect($edition->countryOfManufacture())->toBe('FI');

    // Contact without address information
    $edition = app(EditionService::class)->get('2f5a7df6-2ba9-46f5-b9d8-4ca84c633997');
    expect($edition->countryOfManufacture())->toBeNull();

    // Product with only "Yhteyspainatus" printer
    $edition = app(EditionService::class)->get('43b39da0-5bd7-4b6a-baa7-d9282283fadd');
    expect($edition->countryOfManufacture())->toBeNull();

    // Digital product should return null
    $edition = app(EditionService::class)->get('7b24d4fa-a7fc-46ec-a23c-201d0d49e094');
    expect($edition->countryOfManufacture())->toBeNull();

    // Product with "Yhteispainatus" as the printer that has a country of manufacture
    $edition = app(EditionService::class)->get('a44aa24b-4157-45f7-a34d-8cecee8feb49');
    expect($edition->countryOfManufacture())->toBe('SK');

    $edition = app(EditionService::class)->get('6b0a6e57-8ad1-4e0b-bfde-6b7817e08044');
    expect($edition->countryOfManufacture())->toBe('CN');

    $edition = app(EditionService::class)->get('81621fd3-648f-412a-b08f-ab325c3bdf3e');
    expect($edition->countryOfManufacture())->toBe('FI');
})->group('integration');

it('can get disposition code correctly', function () {
    $edition = app(EditionService::class)->get('81621fd3-648f-412a-b08f-ab325c3bdf3e');
    expect($edition->dispositionCode)->toBe('d');

    // Contact without address information
    $edition = app(EditionService::class)->get('d8a1c1ae-dfe5-476f-88e4-7a3a157dc7d0');
    expect($edition->dispositionCode)->toBe('y');
})->group('integration');

it('can get is translated boolean correctly', function () {
    // Product that is not translated
    $edition = app(EditionService::class)->get('823a10a6-06c7-4aec-9569-b8fa9ce9fc74');
    expect($edition->isTranslated)->toBeFalse();

    // Product that has translator
    $edition = app(EditionService::class)->get('8f242f53-38f4-4231-8e5f-8770ba7cfebe');
    expect($edition->isTranslated)->toBeTrue();

    // Product that has main group "Käännetty L&N" but has no translator
    $edition = app(EditionService::class)->get('bbea8be0-0ed3-4710-a185-4ada1d7d8d94');
    expect($edition->isTranslated)->toBeFalse();

})->group('integration');

it('can handle deleted season data correctly', function () {
    // Product that is not translated
    $edition = app(EditionService::class)->get('f0f99341-af78-465a-9641-11e35d7ec1be');

    expect($edition->season->period)->toBeNull();
    expect($edition->season->year)->toBeNull();

})->group('integration');

it('can handle missing contributor last name correctly', function () {
    // Product that is not translated
    $edition = app(EditionService::class)->get('f0f99341-af78-465a-9641-11e35d7ec1be');

    $contributors = $edition->contributors->toCollection()->where('firstName', 'Keski-Suomen Sivu Oy');

    expect($contributors)->toBeInstanceOf(Collection::class)
        ->and($contributors)->toHaveCount(2)
        ->and($contributors->first()->fullName)->toBe('Keski-Suomen Sivu Oy');

})->group('integration');

it('can handle zero measurement values correctly', function () {
    // Product that is not translated
    $edition = app(EditionService::class)->getByGtin(9789510382233);

    expect($edition->width)->toBeNull();
})->group('integration');

it('can return imprints correctly', function () {
    // Normal WSOY product
    $edition = app(EditionService::class)->getByGtin(9789510366264);
    expect($edition->imprint)->toBeNull();

    // Bazar product without brand
    $edition = app(EditionService::class)->getByGtin(9789522799531);
    expect($edition->imprint)->toBeNull();

    // Readme product with brand "Kids.fi"
    $edition = app(EditionService::class)->getByGtin(9789522563828);
    expect($edition->imprint)->toBe('Kids.fi');

    // Docendo product with brand "CrimeTime"
    $edition = app(EditionService::class)->getByGtin(9789528504627);
    expect($edition->imprint)->toBe('CrimeTime');
})->group('integration');

it('can map contributor roles to Onix codelist values correctly', function () {
    $edition = app(EditionService::class)->getByGtin(9789528504627);

    // Role that does not have an Onix codelist value should return null
    expect($edition->contributors->where('role.name', 'Digital producer')->first()->role->onixCodelistValue)->toBeNull();

    // Author
    expect($edition->contributors->where('role.name', 'Author')->first()->role->onixCodelistValue)->toBe('A01');
})->group('integration');
