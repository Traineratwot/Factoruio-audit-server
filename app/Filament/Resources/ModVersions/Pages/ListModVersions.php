<?php

namespace App\Filament\Resources\ModVersions\Pages;

use App\Filament\Resources\ModVersions\ModVersionResource;
use App\Filament\Traits\InteractsWithScout;
use App\Models\ModVersion;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListModVersions extends ListRecords
{
    use InteractsWithScout;

    protected static string $resource = ModVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('truncateTable')
                ->label('Truncate Table')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Truncate Table')
                ->modalDescription('Are you sure you want to truncate the mod_versions table? This will delete ALL records and cannot be undone.')
                ->modalSubmitActionLabel('Truncate')
                ->action(function (): void {
                    ModVersion::query()->truncate();

                    Notification::make()
                        ->title('Mod versions table truncated')
                        ->success()
                        ->send();
                }),
        ];
    }
}
