<?php

namespace App\Filament\Resources\ModVersions\Pages;

use App\Filament\Resources\ModVersions\ModVersionResource;
use App\Filament\Traits\InteractsWithScout;
use Filament\Resources\Pages\ListRecords;

class ListModVersions extends ListRecords
{
    use InteractsWithScout;

    protected static string $resource = ModVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
