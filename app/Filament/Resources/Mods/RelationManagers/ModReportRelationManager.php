<?php

namespace App\Filament\Resources\Mods\RelationManagers;

use App\Filament\Resources\Reports\Schemas\ReportInfolist;
use App\Filament\Resources\Reports\Tables\ReportsTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ModReportRelationManager extends RelationManager
{
    protected static string $relationship = 'reports';

    public function infolist(Schema $schema): Schema
    {
        return ReportInfolist::configure($schema);
    }

    public function table(Table $table): Table
    {
        return ReportsTable::table($table);
    }
}
