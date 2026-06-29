<?php

namespace App\Filament\Resources\Reports\Tables;

use App\Models\Report;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Number;

class ReportsTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('mod.title')
                    ->url(function (Report $record) {
                        return route('report.mod.version', [
                            'mod' => $record->mod->name,
                            'version' => $record->mod_version
                        ]);
                    }, true)
                    ->label('Mod Name'),

                TextColumn::make('mod_version')
                    ->label('Mod Version'),

                TextColumn::make('score')
                    ->label('Score')
                    ->color(fn(?int $state): string => match (true) {
                        $state >= 80 => 'success',
                        $state >= 50 => 'warning',
                        default => 'danger', // добавлен цвет для низких оценок
                    })
                    ->sortable()
                    ->badge() // визуальное выделение в виде бейджа
                    ->weight('bold') // улучшенная читаемость
                    ->formatStateUsing(fn(float $state): string => Number::forHumans($state, 2))
                ,

                TextColumn::make('sha1')
                    ->label('Sha1'),
            ])
            ->filters([
                //
            ]);
    }
}
