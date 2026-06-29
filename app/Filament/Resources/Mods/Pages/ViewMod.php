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
                ->label('Загрузить полную информацию')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn (Mod $record) => $this->fetchFullInfo($record)),
        ];
    }

    protected function fetchFullInfo(Mod $record): void
    {
        if (! $record->fetchFullInfo()) {
            Notification::make()
                ->title('Ошибка')
                ->body('Не удалось загрузить информацию о моде')
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title('Готово')
            ->body('Полная информация загружена')
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
}
