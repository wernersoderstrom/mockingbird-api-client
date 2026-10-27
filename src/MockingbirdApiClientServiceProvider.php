<?php

namespace Wernersoderstrom\MockingbirdApiClient;

use Wernersoderstrom\MockingbirdApiClient\Editions\EditionService;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
