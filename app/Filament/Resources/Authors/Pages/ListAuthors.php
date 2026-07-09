<?php

namespace App\Filament\Resources\Authors\Pages;

use App\Filament\Resources\Authors\AuthorResource;
use App\Filament\Traits\InteractsWithScout;
use App\Models\Author;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListAuthors extends ListRecords
{
    use InteractsWithScout;

    protected static string $resource = AuthorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('truncateTable')
                ->label('Truncate Table')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Truncate Table')
                ->modalDescription('Are you sure you want to truncate the authors table? This will delete ALL records and cannot be undone.')
                ->modalSubmitActionLabel('Truncate')
                ->action(function (): void {
                    Author::query()->truncate();

                    Notification::make()
                        ->title('Authors table truncated')
                        ->success()
                        ->send();
                }),
        ];
    }
}
