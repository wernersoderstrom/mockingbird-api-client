<?php

namespace Wernersoderstrom\MockingbirdApiClient;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class MockingbirdApiClient
{
    protected function authenticate(): string
    {
        $response = Http::asForm()
            ->post(
                config('mockingbird-api-client.oauth_url'),
                [
                    'grant_type' => 'password',
                    'client_id' => config('mockingbird-api-client.client_id'),
                    'client_secret' => config('mockingbird-api-client.client_secret'),
                    'username' => config('mockingbird-api-client.username'),
                    'password' => config('mockingbird-api-client.password'),
                    'scope' => config('mockingbird-api-client.scope'),
                ]
            )
            ->throw()
            ->json();

        return $response['access_token'];
    }

    protected function accessToken(): string
    {
        return Cache::remember(
            'mockingbird-api-client.access-token',
            now()->addMinutes(50),
            fn () => $this->authenticate()
        );
    }

    protected function request(): PendingRequest
    {
        return Http::baseUrl(config('mockingbird-api-client.base_url'))
            ->acceptJson()
            ->withToken($this->accessToken())
            ->retry(3, 2000)
            ->timeout(5);
    }

    public function get(string $path, array $query = []): array
    {
        return $this->request()
            ->get($path, $query)
            ->throw()
            ->json();
    }

    public function put(string $path, array $payload): array
    {
        return $this->request()
            ->put($path, $payload)
            ->throw()
            ->json();
    }
}
