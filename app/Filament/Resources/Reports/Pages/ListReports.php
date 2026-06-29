<?php

namespace App\Filament\Resources\Reports\Pages;

use App\Filament\Resources\Reports\ReportResource;
use App\Filament\Traits\InteractsWithScout;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReports extends ListRecords
{
    use InteractsWithScout;

    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
