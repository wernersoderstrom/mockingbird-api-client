<?php

namespace Wernersoderstrom\MockingbirdApiClient;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class MockingbirdApiClient
{
    protected function authenticate(): string
    {
        $response = Http::asForm()
            ->post(
                config()->string('mockingbird-api-client.oauth_url'),
                [
                    'grant_type' => 'password',
                    'client_id' => config()->string('mockingbird-api-client.client_id'),
                    'client_secret' => config()->string('mockingbird-api-client.client_secret'),
                    'username' => config()->string('mockingbird-api-client.username'),
                    'password' => config()->string('mockingbird-api-client.password'),
                    'scope' => config()->string('mockingbird-api-client.scope'),
                ]
            )
            ->throw()
            ->json();

        // Refresh five minutes before the token expires
        $ttl = max(($response['expires_in'] ?? 3600) - 300, 60);

        Cache::put($this->cacheKey(), $response['access_token'], $ttl);

        return $response['access_token'];
    }

    protected function accessToken(): string
    {
        return Cache::get($this->cacheKey()) ?? $this->authenticate();
    }

    protected function cacheKey(): string
    {
        return 'mockingbird-api-client.access-token.'.md5(implode('|', [
            config()->string('mockingbird-api-client.oauth_url'),
            config()->string('mockingbird-api-client.client_id'),
            config()->string('mockingbird-api-client.username'),
            config()->string('mockingbird-api-client.scope'),
        ]));
    }

    protected function request(): PendingRequest
    {
        $tokenRefreshed = false;

        return Http::baseUrl(config()->string('mockingbird-api-client.base_url'))
            ->acceptJson()
            ->withToken($this->accessToken())
            ->timeout(5)
            ->retry(
                3,
                fn (int $attempt, Throwable $exception) => $this->isUnauthorized($exception) ? 0 : 2000,
                function (Throwable $exception, PendingRequest $request) use (&$tokenRefreshed): bool {
                    // Token expired or revoked: fetch a new one and retry once
                    if ($this->isUnauthorized($exception)) {
                        if ($tokenRefreshed) {
                            return false;
                        }

                        $tokenRefreshed = true;
                        Cache::forget($this->cacheKey());
                        $request->withToken($this->authenticate());

                        return true;
                    }

                    // Only retry on network errors and server errors, not on 4xx
                    return $exception instanceof ConnectionException
                        || ($exception instanceof RequestException && $exception->response->serverError());
                }
            );
    }

    protected function isUnauthorized(Throwable $exception): bool
    {
        return $exception instanceof RequestException && $exception->response->status() === 401;
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<mixed>
     */
    public function get(string $path, array $query = []): array
    {
        return $this->request()
            ->get($path, $query)
            ->throw()
            ->json() ?? [];
    }

    /**
     * @param  array<mixed>  $payload
     * @return array<mixed>|null
     */
    public function put(string $path, array $payload): ?array
    {
        return $this->request()
            ->put($path, $payload)
            ->throw()
            ->json();
    }
}
