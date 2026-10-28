# Changelog

All notable changes to `mockingbird-api-client` will be documented in this file.

## v0.4.0 - 2026-10-28

### Breaking changes

- Dropped support for Laravel 11. Laravel 12 and 13 are supported.
- `EditionData::$vatPercentage` is renamed to `EditionData::$vatRate`. The value is unchanged: a fraction, e.g. `0.135` for 13.5 %.
- `EditionData::countryOfManufacture()` moved to `EditionService::countryOfManufacture(EditionData $edition)`.
- `EditionData::mainEditionGtin()` now returns `?int` instead of `int`.
- `EditionData::from()` now requires a `work` key with the work data. `EditionService::get()` and `getByGtin()` add it automatically.
- Missing or `null` config values now throw an `InvalidArgumentException` naming the key, instead of failing later in the HTTP request.

### Added

- `Edition` facade for `EditionService`, with the global alias `MockingbirdEdition`.
- `EditionData::printer()` returns the printer contributor.
- `UnsupportedCountry` exception when a printer's country has no ISO 3166 code.
- Generated reference documentation for all data objects in the README.

### Fixed

- Calculated retail prices are no longer off by 10 cents or 1 € for some round reseller prices (e.g. 60.00 → 86.10, 180.00 → 216.90). The calculation now uses integer arithmetic.
- `ContributorData::$fullName` is a real computed property, so it is included in `toArray()`.
- The access token is refreshed and the request retried once when the API returns 401.
- Requests are retried only on connection errors and 5xx responses. 4xx responses fail immediately instead of after about 4 seconds.
- The token cache lifetime follows the token's `expires_in`, and the cache key is separate per environment and client.
- `put()` no longer fails on empty responses.

### Changed

- `EditionData` no longer calls the API. `EditionService` fetches the work and contact data.
- Exceptions are thrown through named constructors (`UnsupportedBindingCode::forCode()`, `forInternalTitle()`).
- PHPStan raised from level 5 to level 10.
