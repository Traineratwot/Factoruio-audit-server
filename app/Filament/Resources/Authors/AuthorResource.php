<?php

namespace App\Filament\Resources\Authors;

use App\Filament\Resources\Authors\RelationManagers\ModsRelationManager;
use App\Filament\Resources\Authors\Schemas\AuthorInfolist;
use App\Filament\Resources\Authors\Tables\AuthorsTable;
use App\Models\Author;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AuthorResource extends Resource
{
    protected static ?string $model = Author::class;

    protected static ?string $slug = 'authors';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;

    protected static ?string $navigationLabel = 'Authors';

    protected static ?string $modelLabel = 'Author';

    protected static ?string $pluralModelLabel = 'Authors';

    public static function infolist(Schema $schema): Schema
    {
        return AuthorInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AuthorsTable::table($table);
    }

    public static function getRelations(): array
    {
        return [
            ModsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuthors::route('/'),
            'edit' => Pages\ViewAuthor::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }
}
