<?php

namespace App\Filament\Resources\Mods\Pages;

use App\Filament\Resources\Mods\ModResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMods extends ListRecords
{
    protected static string $resource = ModResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
