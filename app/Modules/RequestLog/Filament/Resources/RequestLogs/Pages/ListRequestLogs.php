<?php

namespace App\Modules\RequestLog\Filament\Resources\RequestLogs\Pages;

use App\Modules\RequestLog\Filament\Actions\TruncateAction;
use App\Modules\RequestLog\Filament\Resources\RequestLogs\RequestLogResource;
use App\Modules\RequestLog\Models\RequestLog;
use Filament\Resources\Pages\ListRecords;

class ListRequestLogs extends ListRecords
{
    protected static string $resource = RequestLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            TruncateAction::make('trunc')
                ->model(RequestLog::class),
        ];
    }
}
