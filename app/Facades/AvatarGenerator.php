<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \App\Helpers\AvatarGenerator
 */
class AvatarGenerator extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Helpers\AvatarGenerator::class;
    }
}
