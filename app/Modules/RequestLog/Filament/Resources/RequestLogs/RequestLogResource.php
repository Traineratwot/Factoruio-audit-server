<?php

namespace App\Modules\RequestLog\Filament\Resources\RequestLogs;

use App\Modules\RequestLog\Filament\Resources\RequestLogs\Schemas\RequestLogForm;
use App\Modules\RequestLog\Filament\Resources\RequestLogs\Tables\RequestLogsTable;
use App\Modules\RequestLog\Models\RequestLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RequestLogResource extends Resource
{
    protected static ?string $model = RequestLog::class;

    protected static ?string $slug = 'request-logs';

    protected static string|null|BackedEnum $navigationIcon = Heroicon::DocumentArrowUp;

    protected static ?int $navigationSort = 5000;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Request Logs';

    protected static ?string $modelLabel = 'Request Log';

    protected static ?string $pluralModelLabel = 'Request Logs';

    public static function form(Schema $schema): Schema
    {
        return RequestLogForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RequestLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RequestLogsTable::table($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRequestLogs::route('/'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [];
    }
}
