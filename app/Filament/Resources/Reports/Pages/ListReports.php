<?php

namespace App\Filament\Resources\Reports\Pages;

use App\Filament\Resources\Reports\ReportResource;
use App\Models\Report;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListReports extends ListRecords
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('truncateTable')
                ->label('Truncate Table')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Truncate Table')
                ->modalDescription('Are you sure you want to truncate the reports table? This will delete ALL records and cannot be undone.')
                ->modalSubmitActionLabel('Truncate')
                ->action(function (): void {
                    Report::query()->truncate();

                    Notification::make()
                        ->title('Reports table truncated')
                        ->success()
                        ->send();
                }),
        ];
    }
}
