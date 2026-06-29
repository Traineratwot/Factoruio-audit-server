<?php

namespace App\Filament\Resources\Mods;

use App\Filament\Resources\Mods\RelationManagers\ModReportRelationManager;
use App\Filament\Resources\Mods\RelationManagers\ModVersionRelationManager;
use App\Filament\Resources\Mods\Schemas\ModInfolist;
use App\Filament\Resources\Mods\Tables\ModsTable;
use App\Models\Mod;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ModResource extends Resource
{
    protected static ?string $model = Mod::class;

    protected static ?string $slug = 'mods';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function infolist(Schema $schema): Schema
    {
        return ModInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ModsTable::table($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMods::route('/'),
            'view' => Pages\ViewMod::route('/{record}/view'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'title'];
    }

    public static function getRelations(): array
    {
        return [
            ModVersionRelationManager::class,
            ModReportRelationManager::class,
        ];
    }
}
