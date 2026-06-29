<?php

namespace App\Filament\Resources\Mods\RelationManagers;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ModVersionRelationManager extends RelationManager
{
    protected static string $relationship = 'versions';

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('version')
                    ->label('Version')
                    ->badge()
                    ->color('success'),

                TextEntry::make('factorio_version')
                    ->label('Factorio')
                    ->badge()
                    ->color('info'),

                TextEntry::make('file_name')
                    ->label('File'),

                TextEntry::make('download_url')
                    ->label('Download')
                    ->url(),

                TextEntry::make('sha1')
                    ->label('SHA1')
                    ->fontFamily('mono'),

                TextEntry::make('dependencies')
                    ->label('Dependencies')
                    ->badge()
                    ->separator(','),

                TextEntry::make('released_at')
                    ->label('Released')
                    ->dateTime(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('version')
                    ->label('Version')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('factorio_version')
                    ->label('Factorio')
                    ->badge()
                    ->color('info'),

                TextColumn::make('file_name')
                    ->label('File')
                    ->limit(40)
                    ->tooltip(fn (TextColumn $column): ?string => strlen($column->getState()) > 40 ? $column->getState() : null),

                TextColumn::make('sha1')
                    ->label('SHA1')
                    ->limit(12)
                    ->fontFamily('mono'),

                TextColumn::make('dependencies')
                    ->label('Dependencies')
                    ->badge()
                    ->separator(',')
                    ->limitList(3),

                TextColumn::make('released_at')
                    ->label('Released')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('released_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('version')
                    ->label('Version')
                    ->required(),

                TextInput::make('factorio_version')
                    ->label('Factorio Version')
                    ->required(),

                TextInput::make('file_name')
                    ->label('File Name')
                    ->required(),

                TextInput::make('download_url')
                    ->label('Download URL')
                    ->url()
                    ->required(),

                TextInput::make('sha1')
                    ->label('SHA1')
                    ->length(40)
                    ->required(),

                TagsInput::make('dependencies')
                    ->label('Dependencies'),

                DatePicker::make('released_at')
                    ->label('Released At')
                    ->required(),
            ]);
    }
}
