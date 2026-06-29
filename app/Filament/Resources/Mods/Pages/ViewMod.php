<?php

namespace App\Filament\Resources\Mods\Pages;

use App\Filament\Resources\Mods\ModResource;
use App\Models\Mod;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewMod extends ViewRecord
{
    protected static string $resource = ModResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('fetchFullInfo')
                ->label('Fetch Full Info')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Fetch Full Info')
                ->modalDescription('Fetch full information for this mod from the Factorio mod portal.')
                ->action(fn (Mod $record) => $this->fetchFullInfo($record)),

            Action::make('audit')
                ->label('Run Audit')
                ->icon('heroicon-o-magnifying-glass')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Run Audit')
                ->modalDescription('Scan this mod for code quality and security issues.')
                ->action(fn (Mod $record) => $this->runAudit($record)),
        ];
    }

    protected function fetchFullInfo(Mod $record): void
    {
        if (! $record->fetchFullInfo()) {
            Notification::make()
                ->title('Error')
                ->body('Failed to fetch mod information')
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title('Done')
            ->body('Full information fetched successfully')
            ->success()
            ->send();

        $this->refreshFormData([
            'thumbnail',
            'description',
            'homepage',
            'license',
            'tags',
            'images',
            'releases',
            'changelog',
            'score',
            'factorio_version',
            'latest_release_date',
        ]);
    }

    protected function runAudit(Mod $record): void
    {
        try {
            $record->runAudit();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to run audit: '.$e->getMessage())
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title('Audit completed')
            ->success()
            ->send();
    }
}
