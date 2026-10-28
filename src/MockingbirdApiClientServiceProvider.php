<?php

namespace Wernersoderstrom\MockingbirdApiClient;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Wernersoderstrom\MockingbirdApiClient\Editions\EditionService;

class MockingbirdApiClientServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('mockingbird-api-client')->hasConfigFile();

        $this->app->singleton(MockingbirdApiClient::class);
        $this->app->singleton(EditionService::class);
    }
}
