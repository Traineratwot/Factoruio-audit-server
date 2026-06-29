<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FactorioService
{
    public function client(): PendingRequest
    {
        return Http::baseUrl('https://mods.factorio.com/api/');
    }

    /**
     * @throws ConnectionException
     */
    public function mods()
    {
        return collect(Cache::remember('mods', 3600, function () {
            return $this->client()
                ->timeout(120)
                ->get('mods',
                    [
                        'page_size' => 'max',
                    ]
                )->json('results');
        }));
    }

    /**
     * Fetch full mod info from the Factorio Mod Portal API.
     *
     * @throws ConnectionException
     */
    public function modFull(string $name): ?array
    {
        $cacheKey = "mod_full_{$name}";

        return Cache::remember($cacheKey, 3600, function () use ($name) {
            $response = $this->client()
                ->timeout(5)
                ->get("mods/{$name}/full");

            if ($response->failed()) {
                return null;
            }

            return $response->json();
        });
    }
}
