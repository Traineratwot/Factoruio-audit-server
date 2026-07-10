<?php

namespace App\Modules\RequestLog\Filament\Resources\RequestLogs\RelationManagers;

use App\Modules\RequestLog\Filament\Resources\RequestLogs\RequestLogResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class RequestLogRelationManager extends RelationManager
{
    protected static string $relationship = 'requestLogs';

    public function form(Schema $schema): Schema
    {
        return RequestLogResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return RequestLogResource::table($table);
    }
}
