<?php

namespace App\Filament\Resources\Mods\RelationManagers;

use App\Models\ModVersion;
use Filament\Actions\ViewAction;
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
                    ->url(fn(ModVersion $record) => $record->getUrl()),

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
                    ->tooltip(fn(TextColumn $column): ?string => strlen($column->getState()) > 40 ? $column->getState() : null),

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
            ]);
    }
}
