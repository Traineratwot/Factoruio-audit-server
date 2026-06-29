<?php

namespace App\Filament\Resources\Mods\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ModForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required(),

                TextInput::make('title')
                    ->label('Title'),

                Select::make('author_id')
                    ->label('Author')
                    ->relationship('author', 'name')
                    ->searchable()
                    ->preload(),

                TextInput::make('latest_version')
                    ->label('Latest Version'),

                TextInput::make('category')
                    ->label('Category'),

                Textarea::make('summary')
                    ->label('Summary')
                    ->rows(3),

                Textarea::make('description')
                    ->label('Description')
                    ->rows(5),

                TextInput::make('thumbnail')
                    ->label('Thumbnail URL'),

                TextInput::make('homepage')
                    ->label('Homepage')
                    ->url(),

                TextInput::make('downloads_count')
                    ->label('Downloads Count')
                    ->numeric(),

                TextInput::make('popularity')
                    ->label('Popularity')
                    ->numeric(),

                TextInput::make('score')
                    ->label('Score')
                    ->numeric(),

                TextInput::make('factorio_version')
                    ->label('Factorio Version'),

                TextEntry::make('created_at')
                    ->label('Created Date')
                    ->dateTime(),

                TextEntry::make('updated_at')
                    ->label('Last Modified Date')
                    ->dateTime(),
            ]);
    }
}
