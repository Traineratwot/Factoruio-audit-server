<?php

namespace App\Filament\Resources\ModVersions;

use App\Filament\Resources\ModVersions\Schemas\ModVersionInfolist;
use App\Filament\Resources\ModVersions\Tables\ModVersionsTable;
use App\Models\ModVersion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ModVersionResource extends Resource
{
    protected static ?string $model = ModVersion::class;

    protected static ?string $slug = 'mod-versions';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function infolist(Schema $schema): Schema
    {
        return ModVersionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ModVersionsTable::table($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModVersions::route('/'),
        ];
    }

    /**
     * @return Builder<ModVersion>
     */
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['mod']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['mod.name'];
    }

    /**
     * @param ModVersion $record
     */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $details = [];

        if ($record->mod) {
            $details['Mod'] = $record->mod->name;
        }

        return $details;
    }
}
