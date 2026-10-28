<?php

namespace Wernersoderstrom\MockingbirdApiClient\Tests;

use Dotenv\Dotenv;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelData\LaravelDataServiceProvider;
use Wernersoderstrom\MockingbirdApiClient\MockingbirdApiClientServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            MockingbirdApiClientServiceProvider::class,
            LaravelDataServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $dotenv = Dotenv::createImmutable(
            dirname(__DIR__),
            '.env.testing'
        );

        $dotenv->safeLoad();

        $app['config']->set('mockingbird-api-client.base_url', env('MOCKINGBIRD_BASE_URL'));
        $app['config']->set('mockingbird-api-client.oauth_url', env('MOCKINGBIRD_OAUTH_URL'));
        $app['config']->set('mockingbird-api-client.client_id', env('MOCKINGBIRD_CLIENT_ID'));
        $app['config']->set('mockingbird-api-client.client_secret', env('MOCKINGBIRD_CLIENT_SECRET'));
        $app['config']->set('mockingbird-api-client.username', env('MOCKINGBIRD_USERNAME'));
        $app['config']->set('mockingbird-api-client.password', env('MOCKINGBIRD_PASSWORD'));
        $app['config']->set('mockingbird-api-client.scope', env('MOCKINGBIRD_SCOPE'));
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
         foreach (\Illuminate\Support\Facades\File::allFiles(__DIR__ . '/../database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
         }
         */
    }
}
