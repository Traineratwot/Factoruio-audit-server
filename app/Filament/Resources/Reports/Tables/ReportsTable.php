<?php

namespace App\Filament\Resources\Reports\Tables;

use App\Models\Report;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Illuminate\Support\Number;

class ReportsTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make()->iconButton()
            ], RecordActionsPosition::BeforeCells)
            ->columns([
                TextColumn::make('mod.title')
                    ->url(function (Report $record) {
                        return route('report.mod.version', [
                            'mod' => $record->mod->name,
                            'version' => $record->mod_version,
                        ]);
                    }, true)
                    ->searchable()
                    ->label('Mod'),

                TextColumn::make('mod_version')
                    ->label('Version'),

                TextColumn::make('scanner_version')
                    ->label('Scanner Version'),

                TextColumn::make('score')
                    ->label('Score')
                    ->color(fn(?int $state): string => match (true) {
                        $state >= 80 => 'success',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    })
                    ->sortable()
                    ->badge()
                    ->weight('bold')
                    ->formatStateUsing(fn(float $state): string => Number::forHumans($state, 2)),

                TextColumn::make('sha1')
                    ->label('SHA1'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([]);
    }
}
