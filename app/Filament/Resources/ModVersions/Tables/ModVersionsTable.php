<?php

namespace App\Filament\Resources\ModVersions\Tables;

use App\Models\ModVersion;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;

class ModVersionsTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('released_at', 'desc')
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
            ], RecordActionsPosition::BeforeColumns)
            ->columns([
                TextColumn::make('mod.name')
                    ->label('Mod')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('version')
                    ->label('Version')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('file_name')
                    ->label('File')
                    ->limit(40)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) > 40) {
                            return $state;
                        }

                        return null;
                    })
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),

                TextColumn::make('download_url')
                    ->label('Download URL')
                    ->url(function (ModVersion $record): ?string {
                        if (!blank($record->download_url)) {
                            return 'https://mods.factorio.com' . $record->download_url;
                        }

                        return null;
                    })
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),

                TextColumn::make('sha1')
                    ->label('SHA1')
                    ->fontFamily('mono')
                    ->limit(12)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) > 12) {
                            return $state;
                        }

                        return null;
                    })
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),

                TextColumn::make('factorio_version')
                    ->label('Factorio')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('dependencies')
                    ->label('Dependencies')
                    ->badge()
                    ->color('gray')
                    ->limitList(3)
                    ->placeholder('—'),

                TextColumn::make('released_at')
                    ->label('Released')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([]);
    }
}
