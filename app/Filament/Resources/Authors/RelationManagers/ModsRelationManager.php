<?php

namespace App\Filament\Resources\Authors\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ModsRelationManager extends RelationManager
{
    protected static string $relationship = 'mods';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required(),

                TextInput::make('latest_version')
                    ->label('Latest Version'),

                TextInput::make('category')
                    ->label('Category'),

                TextInput::make('title')
                    ->label('Title'),

                TextInput::make('summary')
                    ->label('Summary'),

                TextInput::make('downloads_count')
                    ->label('Downloads Count')
                    ->integer(),

                TextInput::make('popularity')
                    ->label('Popularity')
                    ->numeric(),

                TextEntry::make('created_at')
                    ->label('Created Date')
                    ->dateTime(),

                TextEntry::make('updated_at')
                    ->label('Last Modified Date')
                    ->dateTime(),

                TextInput::make('thumbnail')
                    ->label('Thumbnail'),

                TextInput::make('description')
                    ->label('Description'),

                TextInput::make('homepage')
                    ->label('Homepage'),

                TextInput::make('changelog')
                    ->label('Changelog'),

                TextInput::make('score')
                    ->label('Score')
                    ->numeric(),

                TextInput::make('factorio_version')
                    ->label('Factorio Version'),

                TextInput::make('latest_release_date')
                    ->label('Latest Release Date'),

                Select::make('author_id')
                    ->label('Author Id')
                    ->relationship('author', 'name')
                    ->searchable(),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Name'),

                TextEntry::make('latest_version')
                    ->label('Latest Version'),

                TextEntry::make('category')
                    ->label('Category'),

                TextEntry::make('title')
                    ->label('Title'),

                TextEntry::make('summary')
                    ->label('Summary'),

                TextEntry::make('downloads_count')
                    ->label('Downloads Count'),

                TextEntry::make('popularity')
                    ->label('Popularity'),

                TextEntry::make('created_at')
                    ->label('Created Date')
                    ->dateTime(),

                TextEntry::make('updated_at')
                    ->label('Last Modified Date')
                    ->dateTime(),

                TextEntry::make('thumbnail')
                    ->label('Thumbnail'),

                TextEntry::make('description')
                    ->label('Description'),

                TextEntry::make('homepage')
                    ->label('Homepage'),

                TextEntry::make('license')
                    ->label('License'),

                TextEntry::make('tags')
                    ->label('Tags'),

                TextEntry::make('images')
                    ->label('Images'),

                TextEntry::make('changelog')
                    ->label('Changelog'),

                TextEntry::make('score')
                    ->label('Score'),

                TextEntry::make('factorio_version')
                    ->label('Factorio Version'),

                TextEntry::make('latest_release_date')
                    ->label('Latest Release Date'),

                TextEntry::make('author.name')
                    ->label('Author Id'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('latest_version')
                    ->label('Latest Version'),

                TextColumn::make('category')
                    ->label('Category'),

                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('summary')
                    ->label('Summary'),

                TextColumn::make('downloads_count')
                    ->label('Downloads Count'),

                TextColumn::make('popularity')
                    ->label('Popularity'),

                TextColumn::make('thumbnail')
                    ->label('Thumbnail'),

                TextColumn::make('description')
                    ->label('Description'),

                TextColumn::make('homepage')
                    ->label('Homepage'),

                TextColumn::make('license')
                    ->label('License'),

                TextColumn::make('tags')
                    ->label('Tags'),

                TextColumn::make('images')
                    ->label('Images'),

                TextColumn::make('changelog')
                    ->label('Changelog'),

                TextColumn::make('score')
                    ->label('Score'),

                TextColumn::make('factorio_version')
                    ->label('Factorio Version'),

                TextColumn::make('latest_release_date')
                    ->label('Latest Release Date'),

                TextColumn::make('author.name')
                    ->label('Author Id')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
