<?php

namespace App\Filament\Resources\Mods\RelationManagers;

use App\Filament\Resources\ModVersions\ModVersionResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ModVersionRelationManager extends RelationManager
{
    protected static string $relationship = 'versions';

    protected static ?string $recordTitleAttribute = 'version';

    public function infolist(Schema $schema): Schema
    {
        return ModVersionResource::infolist($schema);
    }

    public function table(Table $table): Table
    {
        return ModVersionResource::table($table);
    }
}
