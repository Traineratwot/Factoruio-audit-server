<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \App\Services\FactorioService
 */
class FactorioService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\FactorioService::class;
    }
}
