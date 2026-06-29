<?php

namespace App\Filament\Resources\Mods\Pages;

use App\Filament\Resources\Mods\ModResource;
use App\Filament\Traits\InteractsWithScout;
use App\Jobs\FetchFullInfoJob;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListMods extends ListRecords
{
    use InteractsWithScout;

    protected static string $resource = ModResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('fetchFullInfo')
                ->label('Fetch Full Info')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->modalHeading('Fetch Full Info')
                ->modalDescription('Dispatch a job to fetch full information for mods.')
                ->schema([
                    Toggle::make('force')
                        ->label('Force (ignore cooldown)')
                        ->default(false),
                    TextInput::make('limit')
                        ->label('Mod limit')
                        ->numeric()
                        ->placeholder('No limit'),
                ])
                ->action(function (array $data): void {
                    FetchFullInfoJob::dispatch(
                        force: $data['force'] ?? false,
                        limit: isset($data['limit']) ? (int) $data['limit'] : null,
                    );

                    Notification::make()
                        ->title('FetchFullInfoJob dispatched')
                        ->success()
                        ->send();
                }),
        ];
    }
}
