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
        return Http::baseUrl("https://mods.factorio.com/api/");
    }

    /**
     * @throws ConnectionException
     */
    public function mods()
    {
        return collect(Cache::remember("mods", 3600, function () {
            return $this->client()
                ->timeout(120)
                ->get('mods',
                    [
                        'page_size' => 'max'
                    ]
                )->json('results');
        }));
    }
}
