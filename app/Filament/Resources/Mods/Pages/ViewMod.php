<?php

namespace App\Filament\Resources\Mods\Pages;

use App\Filament\Resources\Mods\ModResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMod extends ViewRecord
{
    protected static string $resource = ModResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
