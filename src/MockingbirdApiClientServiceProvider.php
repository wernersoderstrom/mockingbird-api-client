<?php

namespace Wernersoderstrom\MockingbirdApiClient;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Wernersoderstrom\MockingbirdApiClient\Editions\EditionService;

class MockingbirdApiClientServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name('mockingbird-api-client')->hasConfigFile();

        $this->app->singleton(MockingbirdApiClient::class);
        $this->app->singleton(EditionService::class);
    }
}
