<?php

namespace App\Filament\Resources\Mods\Pages;

use App\Filament\Resources\Mods\ModResource;
use App\Filament\Traits\InteractsWithScout;
use App\Jobs\FetchFullInfoJob;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListMods extends ListRecords
{
    use InteractsWithScout;

    protected static string $resource = ModResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('fetchFullInfo')
                ->label('Fetch Full Info')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->modalHeading('Обновить полную информацию')
                ->modalDescription('Отправит задачу в очередь на обновление полной информации по модам.')
                ->schema([
                    \Filament\Forms\Components\Toggle::make('force')
                        ->label('Принудительно (игнорировать cooldown)')
                        ->default(false),
                    \Filament\Forms\Components\TextInput::make('limit')
                        ->label('Лимит модов')
                        ->numeric()
                        ->placeholder('Без лимита'),
                ])
                ->action(function (array $data): void {
                    FetchFullInfoJob::dispatch(
                        force: $data['force'] ?? false,
                        limit: isset($data['limit']) ? (int)$data['limit'] : null,
                    );

                    Notification::make()
                        ->title('FetchFullInfoJob отправлен в очередь')
                        ->success()
                        ->send();
                }),
        ];
    }
}
