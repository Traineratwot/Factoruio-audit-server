<?php

namespace App\Filament\Resources\ModVersions\Pages;

use App\Filament\Resources\ModVersions\ModVersionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditModVersion extends EditRecord
{
    protected static string $resource = ModVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
