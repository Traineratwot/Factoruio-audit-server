<?php

namespace App\Filament\Resources\ModVersions\Pages;

use App\Filament\Resources\ModVersions\ModVersionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateModVersion extends CreateRecord
{
    protected static string $resource = ModVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
