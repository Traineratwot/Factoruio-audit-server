<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \App\Services\AuditService
 */
class AuditService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\AuditService::class;
    }
}
