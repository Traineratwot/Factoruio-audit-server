<?php

namespace App\Filament\Resources\ModVersions\Pages;

use App\Filament\Resources\ModVersions\ModVersionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListModVersions extends ListRecords
{
    protected static string $resource = ModVersionResource::class;

    protected static ?string $title = 'Версии модов';

    protected static ?string $description = 'Управление версиями модов Factorio';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
