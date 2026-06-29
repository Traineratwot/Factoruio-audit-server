<?php

namespace App\Filament\Resources\Authors\RelationManagers;

use App\Filament\Resources\Mods\ModResource;
use App\Filament\Resources\Mods\Schemas\ModInfolist;
use App\Filament\Resources\Mods\Tables\ModsTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ModsRelationManager extends RelationManager
{
    protected static string $relationship = 'mods';

    public function form(Schema $schema): Schema
    {
        return ModResource::form($schema);
    }

    public function infolist(Schema $schema): Schema
    {
        return ModInfolist::configure($schema);
    }

    public function table(Table $table): Table
    {
        return ModsTable::table($table);
    }
}
